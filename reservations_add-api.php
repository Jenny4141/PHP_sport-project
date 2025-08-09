<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$output = [
  'success' => false,
  'fail' => false,
  'warning' => false,
  'error' => '',
  'postData' => $_POST
];

$member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
$court_timeslot_id = isset($_POST['court_timeslot_id']) ? intval($_POST['court_timeslot_id']) : 0;
$date = isset($_POST['date']) ? $_POST['date'] : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$status_id = isset($_POST['status_id']) ? intval($_POST['status_id']) : 0;

// 檢查欄位
if ($member_id <= 0 || $court_timeslot_id <= 0 || empty($date) || $price <= 0 || $status_id <= 0) {
  $output['error'] = "請填寫完整資訊";
  echo json_encode($output);
  exit;
}

// 檢查該日期該場地時間是否已被預訂
$checkSql = "SELECT COUNT(1) FROM reservations WHERE court_timeslot_id = ? AND date = ?";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([$court_timeslot_id, $date]);
$exists = $checkStmt->fetchColumn();

if ($exists > 0) {
  $output['fail'] = true;
  $output['error'] = "該場地時間在選定日期已被預訂";
  echo json_encode($output);
  exit;
}

// 插入訂單時存入當前價格
$sql = "INSERT INTO reservations (member_id, court_timeslot_id, date, status_id, price) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

try {
  $stmt->execute([$member_id, $court_timeslot_id, $date, $status_id, $price]);
  $affectedRows = $stmt->rowCount();
  if ($affectedRows > 0) {
    $output['success'] = true;
  } else {
    $output['warning'] = true;
    $output['error'] = "沒有資料修改";
  }
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
