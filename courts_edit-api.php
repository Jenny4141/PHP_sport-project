<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST
];

// 📝 表單欄位檢查
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : "";
$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;
$sport_id = isset($_POST['sport_id']) ? intval($_POST['sport_id']) : 0;

if ($id <= 0) {
  $output['error'] = "缺少有效的 ID";
  echo json_encode($output);
  exit;
}

if (empty($name)) {
  $output['error'] = "請填入場地名稱";
  echo json_encode($output);
  exit;
}

if ($venue_id <= 0) {
  $output['error'] = "請選擇有效的場館";
  echo json_encode($output);
  exit;
}

if ($sport_id <= 0) {
  $output['error'] = "請選擇有效的運動類型";
  echo json_encode($output);
  exit;
}

// 使用 `htmlspecialchars()` 防止 XSS
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

$sql = "UPDATE courts SET name=?, venue_id=?, sport_id=? WHERE id=?";
$courtStmt = $pdo->prepare($sql);

try {
  $courtStmt->execute([$name, $venue_id, $sport_id, $id]);
  $output['success'] = !!$courtStmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
