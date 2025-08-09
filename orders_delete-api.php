<?php
include __DIR__ . '/parts/init.php'; 

header('Content-Type: application/json');

$output = [
    'success' => false,
    'error' => '',
    'message' => '',
    'order_id' => 0, 
];

$input_data = json_decode(file_get_contents("php://input"), true);
$order_id_to_delete = isset($input_data['order_id']) ? intval($input_data['order_id']) : 0;

if ($order_id_to_delete <= 0) {
    $output['error'] = '缺少有效的訂單 ID (order_id)';
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}
$output['order_id'] = $order_id_to_delete;

// --- 開始資料庫交易 ---
$pdo->beginTransaction();

try {
    // 1. 檢查訂單是否存在 (可選步驟，但建議，以提供更明確的錯誤訊息)
    $stmt_check_order = $pdo->prepare("SELECT COUNT(*) FROM `orders` WHERE `order_id` = ?");
    $stmt_check_order->execute([$order_id_to_delete]);
    if ($stmt_check_order->fetchColumn() == 0) {
        // 如果訂單不存在，無需繼續，直接拋出例外讓 catch 區塊處理
        throw new Exception("找不到要刪除的訂單 (ID: {$order_id_to_delete})，可能已被刪除或 ID 無效。");
    }

    // 2. 刪除與該訂單相關的所有商品項目 (從 order_items 表)
    $stmt_delete_items = $pdo->prepare("DELETE FROM `order_items` WHERE `order_id` = ?");
    $stmt_delete_items->execute([$order_id_to_delete]);

    // 3. 刪除訂單主記錄 (從 orders 表)
    $stmt_delete_order = $pdo->prepare("DELETE FROM `orders` WHERE `order_id` = ?");
    $stmt_delete_order->execute([$order_id_to_delete]);

    // 檢查訂單主記錄是否真的被刪除了 (rowCount() > 0 表示有記錄被影響)
    if ($stmt_delete_order->rowCount() > 0) {
        $pdo->commit(); 
        $output['success'] = true;
        $output['message'] = "訂單 (ID: {$order_id_to_delete}) 及其相關的訂單明細已成功刪除。";
    } else {
        throw new Exception("刪除訂單主記錄 (ID: {$order_id_to_delete}) 失敗，該訂單可能在操作過程中已被移除或ID無效。");
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) { 
        $pdo->rollBack(); 
    }
    $output['error'] = '操作失敗：' . $e->getMessage();
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
exit;
