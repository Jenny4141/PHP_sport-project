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
$role = isset($_POST['role']) ? trim($_POST['role']) : "";



if ($id <= 0) {
  $output['error'] = "缺少有效的 ID";
  echo json_encode($output);
  exit;
}

$role = $_POST['role'];
if (empty($role)) {
  $output['error'] = '請選擇角色';
  echo json_encode($output);
  exit;
}

$sql = "UPDATE `members` SET 
    `role`=? 
    WHERE `id`=?";

$memberStmt = $pdo->prepare($sql);

try {
  // $memberStmt->execute([$name, $location_id, $id]);
  $memberStmt->execute([
    $_POST['role'],
    $id
  ]);
  $output['success'] = !!$memberStmt->rowCount();
} catch (PDOException $ex) {
  $output['error'] = "SQL 錯誤：" . $ex->getMessage();
}

echo json_encode($output);
exit;
