<?php
// member-info-api.php

// 引入初始化檔案，通常用於建立資料庫連線 $pdo
include __DIR__ . '/parts/init.php';

// 設定回應的 Content-Type 為 JSON，讓瀏覽器知道這是 JSON 資料
header('Content-Type: application/json');

// 初始化一個輸出陣列，用於回傳給前端的資料
$output = [
    'success' => false, // 預設為失敗
    'member' => null,   // 預設沒有會員資料
    'error' => ''       // 預設沒有錯誤訊息
];

// 取得前端透過 GET 請求傳遞過來的 'id' 參數
// 使用 intval() 確保它是整數，防止 SQL 注入
$memberId = isset($_GET['id']) ? intval($_GET['id']) : 0; // 保持使用 'id' 參數名

// 檢查 $memberId 是否有效 (大於 0)
if ($memberId <= 0) {
    $output['error'] = '無效的會員 ID';
    echo json_encode($output); // 將結果轉換為 JSON 格式並輸出
    exit; // 終止腳本執行
}

try {
    // 準備 SQL 查詢語句
    // 查詢 'members' 資料表中 'id', 'full_name', 'phone', 'email' 欄位
    // 將 full_name 別名為 name，以符合前端期望的 member.name
    $sql = "SELECT id, full_name as name, phone_number as phone, email FROM members WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    // 執行查詢，將 $memberId 綁定到佔位符 ?
    $stmt->execute([$memberId]);

    // 獲取查詢結果
    // PDO::FETCH_ASSOC 表示以關聯陣列 (associative array) 方式獲取資料
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    // 判斷是否成功找到會員資料
    if ($member) {
        $output['success'] = true;      // 標記為成功
        $output['member'] = $member;    // 將查詢到的會員資料放入輸出陣列
    } else {
        $output['error'] = '查無此會員'; // 找不到會員
    }

} catch (PDOException $e) {
    // 捕捉資料庫操作中可能發生的錯誤
    $output['error'] = '資料庫查詢錯誤: ' . $e->getMessage();
    error_log("member-info-api.php PDO Error: " . $e->getMessage()); // 記錄錯誤日誌
} catch (Exception $e) {
    $output['error'] = '伺服器內部錯誤: ' . $e->getMessage();
    error_log("member-info-api.php General Error: " . $e->getMessage());
}

// 將最終的輸出陣列轉換為 JSON 格式並輸出
echo json_encode($output);
exit; // 終止腳本執行