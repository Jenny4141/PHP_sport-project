<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$output = ['success' => false, 'error' => ''];

$court_timeslot_id = isset($_GET['court_timeslot_id']) ? intval($_GET['court_timeslot_id']) : 0;

if ($court_timeslot_id <= 0) {
  $output['error'] = "無效的場地時間 ID";
  echo json_encode($output);
  exit;
}

$priceQuery = "SELECT price FROM courts_timeslots WHERE id = ?";
$priceStmt = $pdo->prepare($priceQuery);
$priceStmt->execute([$court_timeslot_id]);
$price = $priceStmt->fetchColumn();

if ($price !== false) {
  $output['success'] = true;
  $output['price'] = $price;
} else {
  $output['error'] = "價格查詢失敗";
}

echo json_encode($output);
exit;
