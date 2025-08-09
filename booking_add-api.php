<?php
require __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$output = [
    'success' => false,
    'error' => '',
    'postData' => $_POST,
];

$member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
$session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
$booking_status_id = isset($_POST['booking_status_id']) ? intval($_POST['booking_status_id']) : 0;
$price = isset($_POST['price']) ? intval($_POST['price']) : 0;

if ($member_id <= 0 || $session_id <= 0 || !in_array($booking_status_id, [0, 1, 2]) || $price <= 0) {
    $output['error'] = '請填寫所有必要欄位';
    echo json_encode($output);
    exit;
}

$sql = "INSERT INTO booking (member_id, session_id, booking_status_id, price)
        VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        $member_id,
        $session_id,
        $booking_status_id,
        $price
    ]);
    $output['success'] = !!$stmt->rowCount();
} catch (PDOException $ex) {
    $output['error'] = 'SQL 錯誤: ' . $ex->getMessage();
}

echo json_encode($output);
