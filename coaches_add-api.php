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
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : "";
$email = isset($_POST['email']) ? trim($_POST['email']) : "";
$specialty = isset($_POST['specialty']) ? intval($_POST['specialty']) : 0;
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : "";

if (empty($name)) {
  $output['error'] = '請填入場館名稱';
  echo json_encode($output);
  exit;
}
if (empty($phone)) {
  $output['error'] = '請填入場館名稱';
  echo json_encode($output);
  exit;
}
if (empty($email)) {
  $output['error'] = '請填入場館名稱';
  echo json_encode($output);
  exit;
}

if ($specialty <= 0) {
  $output['error'] = '請選擇有效的地區';
  echo json_encode($output);
  exit;
}

if (empty($bio)) {
  $output['error'] = '請填入場館名稱';
  echo json_encode($output);
  exit;
}

// 使用 htmlspecialchars 防止 XSS
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

// 插入資料庫
$sql = "INSERT INTO coaches (coachname_id, email, phone , specialty, bio) VALUES (?, ?, ?, ?, ?)";
$coachStmt = $pdo->prepare($sql);

try {
  $coachStmt->execute([$name, $email, $phone, $specialty ,$bio]);
  $output['success'] = !!$coachStmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
