<?php
require __DIR__ . "/db-connect.php";
# exit;

// 取得 venues 的 id 和名稱
$venues_stmt = $pdo->query("SELECT id, name FROM venues");
// $venues = $venues_stmt->fetchAll(PDO::FETCH_ASSOC);
$venues = array_slice($venues_stmt->fetchAll(PDO::FETCH_ASSOC), 0, 12); // 台北
// $venues = array_slice($venues_stmt->fetchAll(PDO::FETCH_ASSOC), 12, 16); // 新北

// 取得 sports 的 id 和名稱
$sports_stmt = $pdo->query("SELECT id, name FROM sports");
// $sports = $sports_stmt->fetchAll(PDO::FETCH_ASSOC);
$sports = array_slice($sports_stmt->fetchAll(PDO::FETCH_ASSOC), 0, 5); // 籃球 - 排球
// $sports = array_slice($sports_stmt->fetchAll(PDO::FETCH_ASSOC), 11, 5); // 瑜珈 - 韻律

// 準備 SQL 插入 courts 資料
$sql = "INSERT INTO courts (name, venue_id, sport_id) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);

$totalAffectedRows = 0;

// 依據資料庫的 venue_id 和 sport_id 產生場地
foreach ($venues as $venue) {
    foreach ($sports as $sport) {
        for ($i = 1; $i <= 1; $i++) { // 每種運動 n 個場地
            $court_name = "{$venue['name']} {$sport['name']} {$i}";

            // 執行 SQL 插入資料
            $stmt->execute([
                $court_name,
                $venue['id'],  // 正確的場館 id
                $sport['id'],  // 正確的運動 id
            ]);
            $totalAffectedRows += $stmt->rowCount(); // 累計影響筆數
        }
    }
}

// 回傳影響的筆數
echo json_encode([
    "affected_rows" => $totalAffectedRows,
    "last_insert_id" => $pdo->lastInsertId(),
]);
