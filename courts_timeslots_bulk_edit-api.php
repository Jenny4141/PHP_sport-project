<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST
];

$update_type = isset($_POST['update_type']) ? $_POST['update_type'] : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;
$sport_id = isset($_POST['sport_id']) ? intval($_POST['sport_id']) : 0;

if ($price <= 0) {
  $output['error'] = '請填入正確的價格';
  echo json_encode($output);
  exit;
}

// SQL 條件
if ($update_type === "venue" && $venue_id > 0) {
  $sql = "UPDATE courts_timeslots SET price=? WHERE court_id IN (SELECT id FROM courts WHERE venue_id=?)";
  $params = [$price, $venue_id];
} elseif ($update_type === "sport" && $sport_id > 0) {
  $sql = "UPDATE courts_timeslots SET price=? WHERE court_id IN (SELECT id FROM courts WHERE sport_id=?)";
  $params = [$price, $sport_id];
} elseif ($update_type === "time_range") {
  $sql = "UPDATE courts_timeslots SET price=? WHERE time_slot_id IN (SELECT id FROM time_slots WHERE start_time >= ? AND end_time <= ?)";
  $params = [$price, $_POST['start_time'], $_POST['end_time']];
} elseif ($update_type === "time_period") {
  $sql = "UPDATE courts_timeslots SET price=? WHERE time_slot_id IN (SELECT id FROM time_slots WHERE period_id=?)";
  $params = [$price, $_POST['period_id']];
} else {
  $output['error'] = '請選擇有效的修改範圍';
  echo json_encode($output);
  exit;
}

// 使用 `htmlspecialchars()` 防止 XSS
$price = htmlspecialchars($price, ENT_QUOTES, 'UTF-8');

// 執行 SQL 更新
$stmt = $pdo->prepare($sql);
try {
  $stmt->execute($params);
  $output['success'] = !!$stmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
