<?php
require __DIR__ . '/parts/init.php';
header('Content-Type: application/json');

$output = [
    'success' => false,
    'error' => '',
    'postData' => $_POST,
];

$session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
$course_id = isset($_POST['classes_id']) ? intval($_POST['classes_id']) : 0;
$courts_id = isset($_POST['courts_id']) ? intval($_POST['courts_id']) : 0;
$coach_id = isset($_POST['coach_id']) ? intval($_POST['coach_id']) : 0;
$sessions_date = $_POST['sessions_date'] ?? '';
$sessions_time = $_POST['sessions_time'] ?? '';
$price = isset($_POST['price']) ? intval($_POST['price']) : 0;
$max_capacity = isset($_POST['max_capacity']) ? intval($_POST['max_capacity']) : 0;

if ($session_id <= 0) {
    $output['error'] = '缺少有效的課程場次 ID';
    echo json_encode($output);
    exit;
}

if ($course_id <= 0 || $courts_id <= 0 || $coach_id <= 0 || !$sessions_date || !$sessions_time || $price <= 0 || $max_capacity <= 0) {
    $output['error'] = '欄位資料不完整或錯誤';
    echo json_encode($output);
    exit;
}

$sql = "UPDATE `sessions` 
        SET course_id=?, courts_id=?, coach_id=?, sessions_date=?, sessions_time=?, price=?, max_capacity=? 
        WHERE session_id=?";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        $course_id,
        $courts_id,
        $coach_id,
        $sessions_date,
        $sessions_time,
        $price,
        $max_capacity,
        $session_id
    ]);
    $output['success'] = $stmt->rowCount() > 0;
    if (!$output['success']) {
        $output['error'] = '資料未變更';
    }
} catch (PDOException $ex) {
    $output['error'] = '資料庫錯誤: ' . $ex->getMessage();
}

echo json_encode($output);
