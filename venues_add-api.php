<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST
];

// 📝 表單欄位檢查
$name = isset($_POST['name']) ? trim($_POST['name']) : "";
$location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;

if (empty($name)) {
  $output['error'] = '請填入場館名稱';
  echo json_encode($output);
  exit;
}

if ($location_id <= 0) {
  $output['error'] = '請選擇有效的地區';
  echo json_encode($output);
  exit;
}

// 使用 htmlspecialchars 防止 XSS
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

// 插入資料庫
$sql = "INSERT INTO venues (name, location_id) VALUES (?, ?)";
$venueStmt = $pdo->prepare($sql);

try {
  $venueStmt->execute([$name, $location_id]);
  $output['success'] = !!$venueStmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
