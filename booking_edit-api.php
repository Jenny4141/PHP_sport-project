<?php
require __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$output = [
    'success' => false,
    'error' => '',
    'postData' => $_POST,
];

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
$session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
$booking_status_id = isset($_POST['booking_status_id']) ? intval($_POST['booking_status_id']) : 0;
$price = isset($_POST['price']) ? intval($_POST['price']) : 0;

if ($booking_id <= 0 || $member_id <= 0 || $session_id <= 0 || !in_array($booking_status_id, [0, 1, 2]) || $price <= 0) {
    $output['error'] = '請填寫所有欄位';
    echo json_encode($output);
    exit;
}

$sql = "UPDATE booking SET 
          member_id=?, 
          session_id=?, 
          booking_status_id=?, 
          price=? 
        WHERE booking_id=?";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        $member_id,
        $session_id,
        $booking_status_id,
        $price,
        $booking_id
    ]);

    $output['success'] = $stmt->rowCount() > 0;
    if (!$output['success']) {
        $output['error'] = '資料未修改';
    }
} catch (PDOException $ex) {
    $output['error'] = 'SQL 錯誤: ' . $ex->getMessage();
}

echo json_encode($output);
