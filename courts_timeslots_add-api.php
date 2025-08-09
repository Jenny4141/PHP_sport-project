<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST
];

// 📝 表單欄位檢查
$court_id = isset($_POST['court_id']) ? intval($_POST['court_id']) : 0;
$time_slot_id = isset($_POST['time_slot_id']) ? intval($_POST['time_slot_id']) : 0;
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;

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

// 插入資料庫
$sql = "INSERT INTO courts_timeslots (court_id, time_slot_id, price) VALUES (?, ?, ?)";
$courtTimeStmt = $pdo->prepare($sql);

try {
  $courtTimeStmt->execute([$court_id, $time_slot_id, $price]);
  $output['success'] = !!$courtTimeStmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
