<?php

include __DIR__ . '/parts/init.php';
header('Content-Type: application/json');
$output = [
  'success' => false,
  'postData' => $_POST,
  'code' => 0,
  'error' => ''
];

// 1. 檢查必要欄位
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

# 兩個欄位都要有值
if (!$email or !$password) {
  $output['code'] = 400;
  echo json_encode($output);
  exit;
}

// 2. 檢查帳號是否正確
# 找出用戶輸入那筆資料
$sql = "SELECT * FROM members WHERE email=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$row = $stmt->fetch();

# 確認帳號是正確的
if (empty($row)) {
  # 帳號是錯(這欄為空)
  $output['code'] = 410;
  echo json_encode($output);
  exit;
}

$member_id = $row['id'];
// bonus 檢查帳號是否被鎖定
$checkSql="SELECT COUNT(*) FROM login_attempts
WHERE member_id = ? 
AND success = 0
AND attempt_time > (NOW()- INTERVAL 5 MINUTE)";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([$member_id]);
$failCount = $checkStmt->fetchColumn();

if ($failCount > 3) {
  $output["code"] = 450;
  $output['error'] = "帳號已被暫時鎖定,請稍後再試";
  echo json_encode($output);
  exit;
}
// 3. 檢查密碼是否正確1
$result = password_verify($password, $row['password']);

// 3. bonus 記錄登入嘗試
$insertSql = "INSERT INTO login_attempts (member_id, attempt_time, success) VALUES (?, NOW(), ?)";
$insertStmt = $pdo->prepare($insertSql);
$insertStmt->execute([$member_id, $result ? 1 : 0]);

if (! $result) {
  # 密碼是錯的
  $output['code'] = 430;
  echo json_encode($output);
  exit;
}
// 4. 都正確，把狀態記錄到 session
# 呈現給用戶看
$output['success'] = true;
$output['code'] = 200;

# 設定 session
$_SESSION['member'] = [
  'id' => $row['id'],
  'email' => $row['email'],
  'username' => $row['username'],
  'role'=> $row['role'],
  'avatar_url'=> $row['avatar_url'],
];

echo json_encode($output); 
