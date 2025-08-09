<?php
require __DIR__ . "/db-connect.php";
# exit;

// 取得 venues 的 id 和名稱
$venues_stmt = $pdo->query("SELECT id, name FROM venues");
$venues = $venues_stmt->fetchAll(PDO::FETCH_ASSOC);

// 取得 sports 的 id 和時間範圍
$sports_stmt = $pdo->query("SELECT id, name FROM sports");
$sports = $sports_stmt->fetchAll(PDO::FETCH_ASSOC);

// 準備 SQL 插入 venues_sports 資料
$sql = "INSERT INTO venues_sports (venue_id, sport_id) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);

$totalAffectedRows = 0;

// 依據 venues 和 sports 產生組合
foreach ($venues as $venue) {
    $random_sports = array_rand($sports, rand(5, 10));

    foreach ($random_sports as $sport_index) {
        // 執行 SQL 插入資料
        $stmt->execute([
            $venue['id'],               // 正確的場地 ID
            $sports[$sport_index]['id'] // 隨機選擇的運動 ID
        ]);
        $totalAffectedRows += $stmt->rowCount(); // 累計影響筆數
    }
}

// 回傳影響的筆數
echo json_encode([
    "affected_rows" => $totalAffectedRows
]);
?>
