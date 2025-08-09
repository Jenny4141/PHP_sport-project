<?php
require __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$output = [
    'success' => false,
    'error' => '',
    'postData' => $_POST,
];

$class_id = $_POST['classes_id'] ?? 0;
$courts_id = $_POST['courts_id'] ?? 0;
$coach_id = $_POST['coach_id'] ?? 0;
$sessions_date = $_POST['sessions_date'] ?? '';
$sessions_time = $_POST['sessions_time'] ?? '';
$price = $_POST['price'] ?? 0;
$max_capacity = $_POST['max_capacity'] ?? 0;

if (
    $class_id <= 0 || $courts_id <= 0 || $coach_id <= 0 ||
    empty($sessions_date) || empty($sessions_time) ||
    $price <= 0 || $max_capacity <= 0
) {
    $output['error'] = '請確認所有欄位皆正確填寫';
    echo json_encode($output);
    exit;
}

// 插入一筆場次資料（sessions）
$s_sql = "INSERT INTO sessions 
(course_id, courts_id, coach_id, sessions_date, sessions_time, price, max_capacity) 
VALUES (?, ?, ?, ?, ?, ?, ?)";
$s_stmt = $pdo->prepare($s_sql);

try {
    $s_stmt->execute([
        $class_id,
        $courts_id,
        $coach_id,
        $sessions_date,
        $sessions_time,
        $price,
        $max_capacity
    ]);
    $output['success'] = true;
} catch (PDOException $ex) {
    $output['error'] = 'SQL 錯誤：' . $ex->getMessage();
}

echo json_encode($output);
