<?php
require __DIR__ . "/db-connect.php";
# exit;

// 取得 courts 的 id 和名稱
$courts_stmt = $pdo->query("SELECT id, name FROM courts");
$courts = $courts_stmt->fetchAll(PDO::FETCH_ASSOC);
// $courts = array_slice($courts_stmt->fetchAll(PDO::FETCH_ASSOC), 0, 10);

// 取得 time_slots 的 id 和時間範圍
$time_slots_stmt = $pdo->query("SELECT id, start_time, end_time FROM time_slots");
$time_slots = $time_slots_stmt->fetchAll(PDO::FETCH_ASSOC); // 06:00 - 22:00
// $time_slots = array_slice($time_slots_stmt->fetchAll(PDO::FETCH_ASSOC), 2, 8); // 08:00 - 16:00

// 準備 SQL 插入 courts_timeslots 資料
$sql = "INSERT INTO courts_timeslots (court_id, time_slot_id, price) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);

$totalAffectedRows = 0;

// 依據 courts 和 time_slots 產生時段價格
foreach ($courts as $court) {
    foreach ($time_slots as $slot) {
        // $price = rand(100, 500); // 隨機價格設定
        $price = 200; 

        // 執行 SQL 插入資料
        $stmt->execute([
            $court['id'],       // 正確的場地 ID
            $slot['id'],        // 正確的時段 ID
            $price              // 設定價格
        ]);
        $totalAffectedRows += $stmt->rowCount(); // 累計影響筆數
    }
}

// 回傳影響的筆數
echo json_encode([
    "affected_rows" => $totalAffectedRows,
    "last_insert_id" => $pdo->lastInsertId(),
]);
?>
