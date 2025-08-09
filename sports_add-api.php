<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$output = [
  'success' => false,
  'error' => '',
  'id' => 0,
  'name' => '' // 新增回傳 name 欄位
];

// 獲取從請求主體傳來的 JSON 資料
$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? ''); // 從解碼後的資料獲取 name

if (empty($name)) {
  $output['error'] = '請輸入運動種類名稱';
  echo json_encode($output);
  exit;
}

try {
  $stmt = $pdo->prepare("INSERT INTO sports (name) VALUES (?)");
  $stmt->execute([$name]);

  if ($stmt->rowCount()) {
    $output['success'] = true;
    $output['id'] = $pdo->lastInsertId();
    $output['name'] = $name; // 返回新增的名稱
  } else {
    $output['error'] = '新增運動種類失敗，資料庫未回報影響行數。';
  }
} catch (PDOException $ex) {
  // 檢查是否為重複鍵值的錯誤 (SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry)
  if ($ex->getCode() == 23000) {
    $output['error'] = '運動種類名稱已存在，請勿重複新增。';
  } else {
    $output['error'] = '資料庫錯誤：' . $ex->getMessage();
  }
}

echo json_encode($output);
