<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$period = $_GET['range'] ?? '';
$venueId = $_GET['venue_id'] ?? '';
$sportId = $_GET['sport_id'] ?? '';
$date = $_GET['date'] ?? '';

if (empty($period) || empty($venueId) || empty($sportId) || empty($date)) {
  echo json_encode(["success" => false, "message" => "缺少必要參數"]);
  exit;
}


// 取得時間區段的 `period_id`
$periodId = null;
if (!empty($period)) {
  $sqlPeriod = "SELECT id FROM time_periods WHERE name = ?";
  $stmtPeriod = $pdo->prepare($sqlPeriod);
  $stmtPeriod->execute([$period]);
  $periodId = $stmtPeriod->fetchColumn();
}

// ✅ 查詢符合場館+時間區段+運動種類+日期的場地時間
$sql = "SELECT ct.id, c.name AS court_name, ts.start_time, ts.end_time
        FROM courts_timeslots ct
        JOIN courts c ON ct.court_id = c.id
        JOIN time_slots ts ON ct.time_slot_id = ts.id
        LEFT JOIN reservations r ON ct.id = r.court_timeslot_id AND r.date = ?
        WHERE (ts.period_id = ? OR ? IS NULL) 
        AND (c.venue_id = ? OR ? IS NULL) 
        AND (c.sport_id = ? OR ? IS NULL) 
        AND (r.id IS NULL OR r.date != ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$date, $periodId, $periodId, $venueId, $venueId, $sportId, $sportId, $date]);

$output['timeslots'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
$output['success'] = true;

echo json_encode($output);
