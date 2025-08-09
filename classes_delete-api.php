<?php
require __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$output = [
    'success' => false,
    'error' => '',
    'postData' => json_decode(file_get_contents('php://input'), true),
];

// 取得 session_id
$session_id = isset($output['postData']['id']) ? intval($output['postData']['id']) : 0;

if ($session_id <= 0) {
    $output['error'] = '缺少有效的 session_id';
    echo json_encode($output);
    exit;
}

// 刪除資料
$sql = "DELETE FROM sessions WHERE session_id = ?";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$session_id]);
    $output['success'] = $stmt->rowCount() > 0;
    if (!$output['success']) {
        $output['error'] = '找不到對應資料，或已被刪除';
    }
} catch (PDOException $ex) {
    $output['error'] = '資料庫錯誤：' . $ex->getMessage();
}

echo json_encode($output);
