<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST
];

// 📝 表單欄位檢查
$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;
$sport_id = isset($_POST['sport_id']) ? intval($_POST['sport_id']) : 0;

if ($venue_id <= 0) {
  $output['error'] = '請選擇有效的場館';
  echo json_encode($output);
  exit;
}

if ($sport_id <= 0) {
  $output['error'] = '請選擇有效的運動類型';
  echo json_encode($output);
  exit;
}

// 取得場館名稱
$venueStmt = $pdo->prepare("SELECT name FROM venues WHERE id=?");
$venueStmt->execute([$venue_id]);
$venueName = $venueStmt->fetchColumn();

// 取得運動類型名稱
$sportStmt = $pdo->prepare("SELECT name FROM sports WHERE id=?");
$sportStmt->execute([$sport_id]);
$sportName = $sportStmt->fetchColumn();

// 確保場館與運動類型名稱取得成功
if (!$venueName || !$sportName) {
  $output['error'] = '場館或運動類型無效';
  echo json_encode($output);
  exit;
}

// 計算該場館 + 運動類型的場地數量
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM courts WHERE venue_id=? AND sport_id=?");
$countStmt->execute([$venue_id, $sport_id]);
$count = $countStmt->fetchColumn() + 1; // 讓編號從 1 開始

// 生成場地名稱
$courtName = "{$venueName} {$sportName} {$count}";

// 插入資料
$sql = "INSERT INTO courts (name, venue_id, sport_id) VALUES (?, ?, ?)";
$courtStmt = $pdo->prepare($sql);

try {
  $courtStmt->execute([$courtName, $venue_id, $sport_id]);
  $output['success'] = !!$courtStmt->rowCount();
  $output['court_name'] = $courtName; // 回傳場地名稱
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
