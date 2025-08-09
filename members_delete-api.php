<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? intval($data['id']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$output = [
    'success' => false,
    'error' => '',
    'redirectUrl' => "members_list.php?page={$page}&search=" . urlencode($search)
];

if ($id > 0) {
    $sql = "DELETE FROM members WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $output['success'] = $stmt->rowCount() > 0;

    if (!$output['success']) {
        $output['error'] = '刪除失敗或 ID 不存在';
    }
} else {
    $output['error'] = '缺少有效的 ID';
}

echo json_encode($output);
exit;
