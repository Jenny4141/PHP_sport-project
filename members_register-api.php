<?php
require __DIR__ . '/parts/db-connect.php';
$imageBasePath = dirname($_SERVER['PHP_SELF']) . '/db/product_images/';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST
];

// 📝 表單欄位檢查
$username = isset($_POST['username']) ? trim($_POST['username']) : "";
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$avatar_url = isset($_POST['old_avatar_url']) ? $_POST['old_avatar_url'] : '';

if (empty($username)) {
  $output['error'] = '請填入帳號名稱';
  echo json_encode($output);
  exit;
}
if (empty($password)) {
    $output['error'] = '請輸入密碼';
    echo json_encode($output);
    exit;
}
if (empty($fullName)) {
  $output['error'] = '請填入帳號名稱';
  echo json_encode($output);
  exit;
}
if (empty($email)) {
  $output['error'] = '請填入帳號名稱';
  echo json_encode($output);
  exit;
}
$birthDate = null; # 預設值
$timestamp = strtotime($_POST['birth_date']);
if ($timestamp !== false) {
  $birthDate = date('Y-m-d', $timestamp);
}
$gender = $_POST['gender'];
if ($gender === '') {
  $gender = null; 
}
$phone = $_POST['phone_number'];
if ($phone === '') {
  $phone = null; 
}
//密碼轉為雜湊
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// 使用 htmlspecialchars 防止 XSS
$username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

//大頭貼


if (!empty($_FILES['avatar_url']) && $_FILES['avatar_url']['error'] === UPLOAD_ERR_OK) {
  $ext = pathinfo($_FILES['avatar_url']['name'], PATHINFO_EXTENSION);
  $ext = strtolower($ext);
  $allowed = ['jpg', 'jpeg', 'png', 'webp'];

  if (in_array($ext, $allowed)) {
    $folder = __DIR__ . '/uploads/avatars/';
    if (!is_dir($folder)) {
      mkdir($folder, 0755, true);
    }

    $filename = uniqid() . '.' . $ext;
    $targetPath = $folder . $filename;

    if (move_uploaded_file($_FILES['avatar_url']['tmp_name'], $targetPath)) {
      $avatar_url = 'uploads/avatars/' . $filename;
    }
  }
}


// 插入資料庫
$sql = "INSERT INTO `members` (
    `username`,`email`,`password`,`full_name`,`phone_number`,`gender`,`birth_date`,`avatar_url`,`address`) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$memberstmt = $pdo->prepare($sql);

try {
  $memberstmt->execute([
    $username,
    $_POST['email'],
    $hashedPassword,
    $_POST['full_name'],
    $phone,
    $gender,
    $birthDate,
    $avatar_url,
    $_POST['address']]);
  $output['success'] = !!$memberstmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
