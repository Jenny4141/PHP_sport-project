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
$court_id = isset($_POST['court_id']) ? intval($_POST['court_id']) : 0;
$time_slot_id = isset($_POST['time_slot_id']) ? intval($_POST['time_slot_id']) : 0;
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;

if ($id <= 0) {
  $output['error'] = '缺少有效的 ID';
  echo json_encode($output);
  exit;
}

if ($court_id <= 0) {
  $output['error'] = '請選擇有效的場地';
  echo json_encode($output);
  exit;
}

if ($time_slot_id <= 0) {
  $output['error'] = '請選擇有效的時間段';
  echo json_encode($output);
  exit;
}

if ($price <= 0) {
  $output['error'] = '請填入正確的價格';
  echo json_encode($output);
  exit;
}

// 使用 `htmlspecialchars()` 防止 XSS
$price = htmlspecialchars($price, ENT_QUOTES, 'UTF-8');
// 更新資料庫
$sql = "UPDATE courts_timeslots SET court_id=?, time_slot_id=?, price=? WHERE id=?";
$courtTimeStmt = $pdo->prepare($sql);

try {
  $courtTimeStmt->execute([$court_id, $time_slot_id, $price, $id]);
  $output['success'] = !!$courtTimeStmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
