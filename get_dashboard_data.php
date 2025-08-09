<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

// 初始化返回數據
$output = ["success" => false, "error" => "", "data" => []];

try {
  // 1. 各運動場館數量
  $sql1 = "SELECT s.name AS sport_name, COUNT(vs.venue_id) AS venue_count 
             FROM sports s LEFT JOIN venues_sports vs ON s.id = vs.sport_id 
             GROUP BY s.name HAVING venue_count > 0";
  $stmt1 = $pdo->query($sql1);
  $output["data"]["sports_venues"] = $stmt1->fetchAll(PDO::FETCH_ASSOC);

  // 2. 各時段的預訂數量
  $sql2 = "SELECT s.name AS sport_name, COUNT(r.id) AS reservation_count
          FROM reservations r
          JOIN courts_timeslots ct ON r.court_timeslot_id = ct.id
          JOIN courts c ON ct.court_id = c.id
          JOIN sports s ON c.sport_id = s.id
          GROUP BY s.name
          ORDER BY reservation_count DESC;";
  $stmt2 = $pdo->query($sql2);
  $output["data"]["reservations_sports"] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

  // 3. 各時段的預訂數量
  $sql3 = "SELECT ct.time_slot_id AS time_slot, COUNT(r.id) AS reservation_count
             FROM reservations r JOIN courts_timeslots ct ON r.court_timeslot_id = ct.id 
             GROUP BY ct.time_slot_id ORDER BY ct.time_slot_id";
  $stmt3 = $pdo->query($sql3);
  $output["data"]["reservations_timeslots"] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

  // 4. 各場館的收入統計
  $sql4 = "SELECT v.name AS venue_name, SUM(ct.price) AS total_revenue
             FROM reservations r JOIN courts_timeslots ct ON r.court_timeslot_id = ct.id 
             JOIN courts c ON ct.court_id = c.id JOIN venues v ON c.venue_id = v.id 
             GROUP BY v.name";
  $stmt4 = $pdo->query($sql4);
  $output["data"]["venues_revenue"] = $stmt4->fetchAll(PDO::FETCH_ASSOC);

  // 5. 預訂付款狀態
  $sql5 = "SELECT rs.name AS status_name, COUNT(r.id) AS status_count 
             FROM reservations r JOIN reservation_statuses rs ON r.status_id = rs.id 
             GROUP BY rs.name";
  $stmt5 = $pdo->query($sql5);
  $output["data"]["reservations_status"] = $stmt5->fetchAll(PDO::FETCH_ASSOC);

  // 6. 商城出貨狀態
  $sql6 = "SELECT status AS status_name, 
            COUNT(*) AS status_count,
            ROUND((COUNT(*) / (SELECT COUNT(*) FROM orders)) * 100, 2) AS percentage
            FROM orders
            GROUP BY status";
  $stmt6 = $pdo->query($sql6);
  $output["data"]["orders_status_percentage"] = $stmt6->fetchAll(PDO::FETCH_ASSOC);

  $output["success"] = true;
} catch (PDOException $ex) {
  $output["error"] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
