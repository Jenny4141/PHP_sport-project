<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? intval($data['id']) : 0; // 單選
$ids = isset($data['ids']) ? $data['ids'] : []; // 多選
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$output = [
    'success' => false,
    'error' => '',
    'redirectUrl' => "timeslots_list.php?page={$page}&search=" . urlencode($search)
];

try {
    if (!empty($ids)) { // 多選刪除
        $sql = "DELETE FROM time_slots WHERE id IN (" . implode(",", array_fill(0, count($ids), "?")) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
        $output['success'] = $stmt->rowCount() > 0;
    } elseif ($id > 0) { // 單選刪除
        $sql = "DELETE FROM time_slots WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $output['success'] = $stmt->rowCount() > 0;
    } else {
        throw new Exception("缺少有效的 ID");
    }
} catch (Exception $e) {
    $output['error'] = $e->getMessage();
}

// ✅ 確保輸出 JSON，而不是 HTML
echo json_encode($output);
exit;
