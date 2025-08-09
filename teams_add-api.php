<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'errors' => [], // **新增：用於存放多個欄位的錯誤訊息**
  'postData' => $_POST
];

// 📝 表單欄位檢查
$name = isset($_POST['name']) ? trim($_POST['name']) : "";
// $sport_id = isset($_POST['sport_id']) ? intval($_POST['sport_id']) : 0;
$courts_id = isset($_POST['courts_id']) ? intval($_POST['courts_id']) : 0;
$level_id = isset($_POST['level_id']) ? intval($_POST['level_id']) :0;


if (empty($name)) {
    $output['errors']['name'] = '請填入隊伍名稱';
}

if ($courts_id <= 0) {
    $output['errors']['courts_id'] = '請選擇團練場地'; // **修改：錯誤訊息對應的 key**
}

if ($level_id <= 0) {
    $output['errors']['level_id'] = '請選擇隊伍級別';
}

// 如果有任何錯誤，直接輸出並退出
if (!empty($output['errors'])) {
    $output['error'] = '表單資料驗證失敗'; // 總體錯誤訊息
    echo json_encode($output);
    exit;
}


// 使用 htmlspecialchars 防止 XSS
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

// 插入資料庫
$sql = "INSERT INTO teams (name, courts_id, level_id, member_count, created_at) VALUES (?, ?, ?, 0, NOW())";
$teamStmt = $pdo->prepare($sql);

try {
  $teamStmt->execute([$name, $courts_id, $level_id]);
  $output['success'] = !!$teamStmt->rowCount();
  if ($output['success']) {
      $output['team_id'] = $pdo->lastInsertId();
  }
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
