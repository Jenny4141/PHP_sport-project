<?php
// tmember_add-api.php

// 引入初始化檔案，用於建立資料庫連線 $pdo
include __DIR__ . '/parts/init.php';

// 設定回應的 Content-Type 為 JSON
header('Content-Type: application/json');

// 初始化輸出陣列
$output = [
    'success' => false,
    'error' => '',
    'added_members_count' => 0, // 成功新增的成員數量
    'duplicate_members' => [],  // 已存在於隊伍中的成員 ID
    'invalid_members' => []     // 無效或查無的成員 ID (前端已檢查過，但後端仍可再次驗證)
];

// 確保接收到的是 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $output['error'] = '只允許 POST 請求';
    echo json_encode($output);
    exit;
}

// 接收前端傳送的 JSON 資料
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true); // true 轉換為關聯陣列

// 檢查是否成功解析 JSON
if ($data === null) {
    $output['error'] = '無效的 JSON 資料';
    echo json_encode($output);
    exit;
}

$teamId = isset($data['team_id']) ? intval($data['team_id']) : 0;
$memberIds = isset($data['member_ids']) ? $data['member_ids'] : [];

// 檢查 team_id 是否有效
if ($teamId <= 0) {
    $output['error'] = '無效的隊伍 ID';
    echo json_encode($output);
    exit;
}

// 檢查 member_ids 是否為陣列且不為空
if (!is_array($memberIds) || empty($memberIds)) {
    $output['error'] = '請提供有效的成員 ID 陣列';
    echo json_encode($output);
    exit;
}

try {
    $addedCount = 0;
    $duplicateMembers = [];

    // 開始事務 (Transaction)
    $pdo->beginTransaction();

    // 準備查詢成員是否已在隊伍中的 SQL 語句
    // 表名從 team_members 改為 tmember
    // 欄位名從 member_id 改為 members_id
    $checkSql = "SELECT COUNT(*) FROM `tmember` WHERE `team_id` = ? AND `members_id` = ?"; // <--- 修正這行
    $checkStmt = $pdo->prepare($checkSql);

    // 準備插入新成員的 SQL 語句
    // 表名從 team_members 改為 tmember
    // 欄位名從 member_id 改為 members_id
    $insertSql = "INSERT IGNORE INTO `tmember` (`team_id`, `members_id`) VALUES (?, ?)"; // <--- 修正這行
    $insertStmt = $pdo->prepare($insertSql);

    foreach ($memberIds as $memberId) {
        $mid = intval($memberId); // 確保 memberId 是整數

        if ($mid <= 0) {
            $output['invalid_members'][] = $memberId; // 記錄無效 ID
            continue; // 跳過無效 ID
        }

        // 1. 檢查成員是否已經在隊伍中
        $checkStmt->execute([$teamId, $mid]);
        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            $duplicateMembers[] = $mid; // 記錄重複的成員
            continue; // 跳過已存在的成員
        }

        // 2. 插入成員到隊伍中
        $insertStmt->execute([$teamId, $mid]);
        if ($insertStmt->rowCount() > 0) {
            $addedCount++;
        }
    }

    // 提交事務
    $pdo->commit();

    $output['success'] = true;
    $output['added_members_count'] = $addedCount;
    $output['duplicate_members'] = $duplicateMembers;

    if ($addedCount === 0 && !empty($duplicateMembers)) {
        $output['error'] = '所有提交的成員都已在隊伍中。';
    } elseif ($addedCount === 0 && empty($duplicateMembers) && !empty($memberIds)) {
        // 如果 memberIds 有值，但沒有新增成功也沒有重複，可能是有其他問題
        $output['error'] = '沒有新的成員被成功新增，請檢查資料庫操作或會員 ID 是否有效。';
    }

} catch (PDOException $e) {
    // 如果發生錯誤，回滾事務，撤銷所有已執行的資料庫操作
    $pdo->rollBack();
    $output['error'] = '資料庫操作失敗: ' . $e->getMessage();
    error_log("tmember_add-api.php PDO Error: " . $e->getMessage()); // 記錄到 PHP 錯誤日誌
} catch (Exception $e) {
    $pdo->rollBack();
    $output['error'] = '伺服器內部錯誤: ' . $e->getMessage();
    error_log("tmember_add-api.php General Error: " . $e->getMessage()); // 記錄到 PHP 錯誤日誌
}

echo json_encode($output);
exit;