<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST
];

// 📝 表單欄位檢查
$start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : "";
$end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : "";
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;

if (empty($start_time) || empty($end_time)) {
  $output['error'] = '請填入完整的開始和結束時間';
  echo json_encode($output);
  exit;
}

if ($period_id <= 0) {
  $output['error'] = '請選擇有效的時段類別';
  echo json_encode($output);
  exit;
}

// 使用 htmlspecialchars 防止 XSS
$start_time = htmlspecialchars($start_time, ENT_QUOTES, 'UTF-8');
$end_time = htmlspecialchars($end_time, ENT_QUOTES, 'UTF-8');

// 插入資料庫
$sql = "INSERT INTO time_slots (start_time, end_time, period_id) VALUES (?, ?, ?)";
$timeSlotStmt = $pdo->prepare($sql);

try {
  $timeSlotStmt->execute([$start_time, $end_time, $period_id]);
  $output['success'] = !!$timeSlotStmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
