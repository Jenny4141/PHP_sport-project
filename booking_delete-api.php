<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$output = [
    'success' => false,
    'error' => '',
];

// 接收 POST 的 JSON 資料
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !intval($input['id'])) {
    $output['error'] = '缺少有效的 booking_id';
    echo json_encode($output);
    exit;
}

$id = intval($input['id']);

$sql = "DELETE FROM booking WHERE booking_id = ?";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$id]);
    $output['success'] = !!$stmt->rowCount();
    if (!$output['success']) {
        $output['error'] = '查無此資料或資料已刪除';
    }
} catch (PDOException $ex) {
    $output['error'] = '資料庫錯誤: ' . $ex->getMessage();
}

echo json_encode($output);
