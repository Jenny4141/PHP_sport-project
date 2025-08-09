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
$username = isset($_POST['username']) ? trim($_POST['username']) : "";
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$avatar_url = isset($_POST['old_avatar_url']) ? $_POST['old_avatar_url'] : '';


if ($id <= 0) {
  $output['error'] = "缺少有效的 ID";
  echo json_encode($output);
  exit;
}
if (empty($username)) {
  $output['error'] = '請填入帳號名稱';
  echo json_encode($output);
  exit;
}
// if (strlen($password) < 6) {
//   $output['error'] = '密碼長度需至少 6 個字';
//   echo json_encode($output);
//   exit;
// }
if (empty($fullName)) {
  $output['error'] = '請填入姓名';
  echo json_encode($output);
  exit;
}
if (empty($email)) {
  $output['error'] = '請填入帳號名稱';
  echo json_encode($output);
  exit;
}
if (!empty($password)) {
  if (strlen($password) < 6) {
    $output['error'] = '密碼長度需至少 6 個字';
    echo json_encode($output);
    exit;
  } else {
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
  }
} else {
  // 沒填密碼時就用原本的密碼（從資料庫撈）
  $sql = "SELECT password FROM members WHERE id=?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$id]);
  $hashedPassword = $stmt->fetchColumn();
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
// $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// 使用 `htmlspecialchars()` 防止 XSS
$username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');

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


//更新

$sql = "UPDATE `members` SET 
    `username`=?,
    `email`=?,
    `password`=?,
    `full_name`=?,
    `phone_number`=?,
    `gender`=?,
    `birth_date`=?,
    `avatar_url`=?,
    `address`=?
    WHERE `id`=?";

$memberStmt = $pdo->prepare($sql);

try {
  // $memberStmt->execute([$name, $location_id, $id]);
  $memberStmt->execute([
    $username,
    $_POST['email'],
    $hashedPassword,
    $_POST['full_name'],
    $phone,
    $gender,
    $birthDate,
    $avatar_url,
    $_POST['address'],
    $id
  ]);
  $output['success'] = true;
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
