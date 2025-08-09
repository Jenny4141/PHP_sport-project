<?php
require __DIR__ . "/db-connect.php";
# exit;

// 抓取可用的 member_id、court_timeslot_id、status_id
$memberIds = $pdo->query("SELECT id FROM members")->fetchAll(PDO::FETCH_COLUMN);
$courtTimeslotIds = $pdo->query("SELECT id FROM courts_timeslots")->fetchAll(PDO::FETCH_COLUMN);
$statusIds = $pdo->query("SELECT id FROM reservation_statuses")->fetchAll(PDO::FETCH_COLUMN);

$totalAffectedRows = 0;

function getRandomDateWithin30Days()
{
    $timestamp = rand(strtotime('-30 days'), strtotime('+30 days'));
    return date('Y-m-d', $timestamp);
}

function getRandomPrice()
{
    return round(rand(1000, 5000) / 10, 2); // 產生 100.00 ~ 500.00 的價格
}

$insertStmt = $pdo->prepare("
    INSERT INTO reservations (member_id, court_timeslot_id, date, status_id, price)
    VALUES (:member_id, :court_timeslot_id, :date, :status_id, :price)
");

for ($i = 0; $i < 500; $i++) {
    $memberId = $memberIds[array_rand($memberIds)];
    $courtTimeslotId = $courtTimeslotIds[array_rand($courtTimeslotIds)];
    $statusId = $statusIds[array_rand($statusIds)];
    $date = getRandomDateWithin30Days();
    $price = getRandomPrice(); 

    $insertStmt->execute([
        ':member_id' => $memberId,
        ':court_timeslot_id' => $courtTimeslotId,
        ':date' => $date,
        ':status_id' => $statusId,
        ':price' => $price 
    ]);

    $totalAffectedRows += $insertStmt->rowCount(); // 累計影響筆數
}

// 回傳影響的筆數
echo json_encode([
    "affected_rows" => $totalAffectedRows,
    "last_insert_id" => $pdo->lastInsertId(),
]);
