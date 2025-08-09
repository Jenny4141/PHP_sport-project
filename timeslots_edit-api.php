<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST
];

// 📝 表單欄位檢查
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : "";
$end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : "";
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;

if ($id <= 0) {
  $output['error'] = "缺少有效的 ID";
  echo json_encode($output);
  exit;
}

if (empty($start_time)) {
  $output['error'] = "請填入開始時間";
  echo json_encode($output);
  exit;
}

if (empty($end_time)) {
  $output['error'] = "請填入結束時間";
  echo json_encode($output);
  exit;
}

if ($period_id <= 0) {
  $output['error'] = "請選擇有效的時間區段";
  echo json_encode($output);
  exit;
}

// 確保時間格式正確
if (
  !preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/", $start_time) ||
  !preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/", $end_time)
) {
  $output['error'] = "時間格式錯誤";
  echo json_encode($output);
  exit;
}

/* // 使用 `htmlspecialchars()` 防止 XSS ps.這裡不用
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); */

$sql = "UPDATE time_slots SET start_time=?, end_time=?, period_id=? WHERE id=?";
$timeStmt = $pdo->prepare($sql);

try {
  $timeStmt->execute([$start_time, $end_time, $period_id, $id]);
  $output['success'] = !!$timeStmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
