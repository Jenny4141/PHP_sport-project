<?php
// teams-edit-api.php

include __DIR__ . '/parts/init.php';
header('Content-Type: application/json');

$output = [
    'success' => false,
    'error' => '',
    'errors' => [],
    'tmember_id' => 0 // For add-member action
];

$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? null) : ($_GET['action'] ?? null);

// 如果是 POST 請求，先處理 JSON 數據
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() === JSON_ERROR_NONE) {
    // 如果成功解析 JSON，並且 JSON 中有 action，則優先使用 JSON 中的 action
    if (isset($input['action'])) {
        $action = $input['action'];
    }
}

// 根據不同的 action 執行不同的操作
switch ($action) {
    case 'edit-team':
        handleEditTeam();
        break;
    case 'add-member':
        handleAddMember($input); // 傳入解析後的 JSON 數據
        break;
    case 'remove-member':
        handleRemoveMember($input); // 傳入解析後的 JSON 數據
        break;
    case 'delete-team': // 從 teams_list.php 發送過來的刪除請求
        handleDeleteTeam($input);
        break;
    default:
        $output['error'] = '無效的操作或請求方法';
        echo json_encode($output);
        exit;
}

function handleEditTeam()
{
    global $pdo, $output;

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0) {
        $output['error'] = '無效的隊伍 ID';
        echo json_encode($output);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $levelId = isset($_POST['level_id']) ? intval($_POST['level_id']) : 0;
    $courtsId = isset($_POST['courts_id']) ? intval($_POST['courts_id']) : 0; // 使用 courts_id
    $sportId = isset($_POST['sport_id']) ? intval($_POST['sport_id']) : 0;

    // 驗證數據
    if (empty($name)) {
        $output['errors']['name'] = '隊伍名稱不能為空';
        $output['error'] = '驗證失敗';
    }
    if ($levelId <= 0) {
        $output['errors']['level_id'] = '請選擇正確的隊伍等級';
        $output['error'] = '驗證失敗';
    }
    if ($courtsId <= 0) { // 驗證 courts_id
        $output['errors']['courts_id'] = '請選擇所屬場地';
        $output['error'] = '驗證失敗';
    }
    // if ($sportId <= 0) {
    //     $output['errors']['sport_id'] = '請選擇運動類型';
    //     $output['error'] = '驗證失敗';
    // }

    if (!empty($output['errors'])) {
        echo json_encode($output);
        exit;
    }

    // 執行更新
    $sql = "UPDATE teams SET
                name=?,
                level_id=?,
                courts_id=?             
            WHERE id=?";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            $name,
            $levelId,
            $courtsId,
            // $sportId,
            $id
        ]);

        if ($stmt->rowCount()) {
            $output['success'] = true;
        } else {
            $output['error'] = '資料沒有異動';
        }
    } catch (PDOException $e) {
        $output['error'] = '資料庫更新錯誤: ' . $e->getMessage();
        error_log("teams-edit-api.php handleEditTeam PDO Error: " . $e->getMessage());
    }
    echo json_encode($output);
}

function handleAddMember($input)
{
    global $pdo, $output;

    $teamId = isset($input['team_id']) ? intval($input['team_id']) : 0;
    $memberId = isset($input['member_id']) ? intval($input['member_id']) : 0;

    if ($teamId <= 0 || $memberId <= 0) {
        $output['error'] = '無效的隊伍ID或會員ID';
        echo json_encode($output);
        exit;
    }

    // 檢查隊伍成員數量是否已達上限 (8人)
    $countSql = "SELECT COUNT(*) FROM tmember WHERE team_id = ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$teamId]);
    $currentMemberCount = $countStmt->fetchColumn();

    if ($currentMemberCount >= 8) {
        $output['error'] = '隊伍成員已達上限 (8人)，無法新增。';
        echo json_encode($output);
        exit;
    }

    // 檢查是否已存在
    $checkSql = "SELECT COUNT(*) FROM tmember WHERE team_id = ? AND members_id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$teamId, $memberId]);
    if ($checkStmt->fetchColumn() > 0) {
        $output['error'] = '該會員已在隊伍中。';
        echo json_encode($output);
        exit;
    }

    $sql = "INSERT INTO tmember (team_id, members_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$teamId, $memberId]);
        if ($stmt->rowCount()) {
            $output['success'] = true;
            $output['tmember_id'] = $pdo->lastInsertId(); // 返回新插入的 tmember ID

            $updateCountSql = "UPDATE teams SET member_count = member_count + 1 WHERE id = ?";
            $updateCountStmt = $pdo->prepare($updateCountSql);
            $updateCountStmt->execute([$teamId]);
        } else {
            $output['error'] = '加入隊員失敗';
        }
    } catch (PDOException $e) {
        $output['error'] = '資料庫加入隊員錯誤: ' . $e->getMessage();
        error_log("teams-edit-api.php handleAddMember PDO Error: " . $e->getMessage());
    }
    echo json_encode($output);
}

function handleRemoveMember($input)
{
    global $pdo, $output;

    $tmemberId = isset($input['tmember_id']) ? intval($input['tmember_id']) : 0;

    if ($tmemberId <= 0) {
        $output['error'] = '無效的隊伍成員 ID';
        echo json_encode($output);
        exit;
    }

    try {
        $pdo->beginTransaction(); // 開始事務

        // 1. 先取得該 tmember 的 team_id
        $get_team_id_sql = "SELECT team_id FROM tmember WHERE id = ?";
        $get_team_id_stmt = $pdo->prepare($get_team_id_sql);
        $get_team_id_stmt->execute([$tmemberId]);
        $teamId = $get_team_id_stmt->fetchColumn();

        if (!$teamId) {
            // 如果找不到 team_id，表示該 tmember_id 不存在或已移除，直接成功返回
            $output['success'] = true;
            $output['error'] = '隊員可能已移除或不存在，無需重複操作。';
            $pdo->rollBack(); // 回滾事務，因為沒有實際操作
            echo json_encode($output);
            exit;
        }

        // 2. 執行刪除 tmember 記錄
        $sql = "DELETE FROM tmember WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tmemberId]);

        if ($stmt->rowCount()) {
            // 3. 如果刪除成功，更新 teams 表的 member_count
            $updateCountSql = "UPDATE teams SET member_count = member_count - 1 WHERE id = ?";
            $updateCountStmt = $pdo->prepare($updateCountSql);
            $updateCountStmt->execute([$teamId]);

            $output['success'] = true;
        } else {
            // 如果 rowCount() 為 0，表示沒有任何行受影響，可能已經刪除
            $output['success'] = true; // 視為成功，因為目標已經達到 (隊員不存在了)
            $output['error'] = '隊員可能已移除或不存在，無需重複操作。';
        }

        $pdo->commit(); // 提交事務

    } catch (PDOException $e) {
        $pdo->rollBack(); // 發生錯誤時回滾事務
        $output['error'] = '資料庫移除隊員錯誤: ' . $e->getMessage();
        error_log("teams-edit-api.php handleRemoveMember PDO Error: " . $e->getMessage());
    }
    echo json_encode($output);
}

function handleDeleteTeam($input)
{
    global $pdo, $output;

    $teamId = isset($input['id']) ? intval($input['id']) : 0;

    if ($teamId <= 0) {
        $output['error'] = '無效的隊伍 ID';
        echo json_encode($output);
        exit;
    }

    try {
        // 先刪除所有與該隊伍相關的隊員記錄
        $deleteMembersSql = "DELETE FROM tmember WHERE team_id = ?";
        $deleteMembersStmt = $pdo->prepare($deleteMembersSql);
        $deleteMembersStmt->execute([$teamId]);

        // 再刪除隊伍本身
        $deleteTeamSql = "DELETE FROM teams WHERE id = ?";
        $deleteTeamStmt = $pdo->prepare($deleteTeamSql);
        $deleteTeamStmt->execute([$teamId]);

        if ($deleteTeamStmt->rowCount()) {
            $output['success'] = true;
        } else {
            $output['error'] = '刪除隊伍失敗或隊伍不存在';
        }
    } catch (PDOException $e) {
        $output['error'] = '資料庫刪除錯誤: ' . $e->getMessage();
        error_log("teams-edit-api.php handleDeleteTeam PDO Error: " . $e->getMessage());
    }
    echo json_encode($output);
}
