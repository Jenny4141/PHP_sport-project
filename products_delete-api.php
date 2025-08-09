<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');

$input_data = json_decode(file_get_contents("php://input"), true);
$spec_id_to_delete = isset($input_data['id']) ? intval($input_data['id']) : 0;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$output = [
    'success' => false,
    'error' => '',
    'message' => '',
    'redirectUrl' => "products_list.php?page={$page}&search=" . urlencode($search)
];

if ($spec_id_to_delete <= 0) {
    $output['error'] = '缺少有效的規格 ID';
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt_get_spec_details = $pdo->prepare("SELECT product_id FROM specs WHERE spec_id = ?");
    $stmt_get_spec_details->execute([$spec_id_to_delete]);
    $spec_details = $stmt_get_spec_details->fetch(PDO::FETCH_ASSOC);

    if (!$spec_details) {
        throw new Exception("找不到要刪除的規格 (ID: {$spec_id_to_delete})。");
    }
    $product_id_of_spec = $spec_details['product_id'];

    $stmt_update_oi_status_spec = $pdo->prepare(
        "UPDATE order_items SET item_status = 'spec_removed'
         WHERE spec_id = ? AND item_status = 'active'"
    );
    $stmt_update_oi_status_spec->execute([$spec_id_to_delete]);
    $output['message'] .= $stmt_update_oi_status_spec->rowCount() . " 個訂單明細項目因規格移除而更新狀態。\n";

    $stmt_delete_spec = $pdo->prepare("DELETE FROM specs WHERE spec_id = ?");
    $stmt_delete_spec->execute([$spec_id_to_delete]);

    if ($stmt_delete_spec->rowCount() > 0) {
        $output['message'] .= "規格 (ID: {$spec_id_to_delete}) 已成功刪除。\n";
    } else {
        $output['message'] .= "嘗試刪除規格 (ID: {$spec_id_to_delete})，但未找到記錄或已被其他操作移除。\n";
    }

    $stmt_check_specs = $pdo->prepare("SELECT COUNT(*) FROM specs WHERE product_id = ?");
    $stmt_check_specs->execute([$product_id_of_spec]);
    $remaining_specs_count = $stmt_check_specs->fetchColumn();

    if ($remaining_specs_count == 0) {
        $output['message'] .= "這是商品 (ID: {$product_id_of_spec}) 的最後一個規格，將一併刪除商品資料及其所有圖片。\n";

        $stmt_get_prod_name = $pdo->prepare("SELECT name FROM products WHERE product_id = ?");
        $stmt_get_prod_name->execute([$product_id_of_spec]);
        $prod_to_delete_info = $stmt_get_prod_name->fetch(PDO::FETCH_ASSOC);

        if ($prod_to_delete_info) {
            $product_name_to_mark = $prod_to_delete_info['name'];
            $stmt_update_oi_status_prod = $pdo->prepare(
                "UPDATE order_items SET item_status = 'product_removed'
                 WHERE ordered_product_name = ? AND (item_status = 'active' OR item_status = 'spec_removed')"
            );
            $stmt_update_oi_status_prod->execute([$product_name_to_mark]);
            $output['message'] .= $stmt_update_oi_status_prod->rowCount() . " 個訂單明細項目因商品移除而更新狀態。\n";
        }

        $stmt_get_images = $pdo->prepare("SELECT image_id, image_url FROM images WHERE product_id = ?");
        $stmt_get_images->execute([$product_id_of_spec]);
        $images_to_delete = $stmt_get_images->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($images_to_delete)) {
            $image_dir = __DIR__ . '/db/product_images/';
            foreach ($images_to_delete as $img) {
                if (!empty($img['image_url'])) {
                    $filepath = $image_dir . $img['image_url'];
                    if (file_exists($filepath) && is_writable($filepath)) {
                        @unlink($filepath);
                    }
                }
            }
            $stmt_delete_img_db = $pdo->prepare("DELETE FROM images WHERE product_id = ?");
            $stmt_delete_img_db->execute([$product_id_of_spec]);
            $output['message'] .= "商品 (ID: {$product_id_of_spec}) 的相關圖片資料庫記錄已刪除。\n";
        } else {
            $output['message'] .= "商品 (ID: {$product_id_of_spec}) 沒有關聯的圖片記錄可刪除。\n";
        }

        $stmt_delete_product = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt_delete_product->execute([$product_id_of_spec]);
        if ($stmt_delete_product->rowCount() > 0) {
            $output['message'] .= "商品 (ID: {$product_id_of_spec}) 主記錄已成功刪除。\n";
        } else {
            $output['message'] .= "嘗試刪除商品 (ID: {$product_id_of_spec}) 主記錄，但可能已被刪除或不存在。\n";
        }
    } else {
        $output['message'] .= "商品 (ID: {$product_id_of_spec}) 尚有其他 {$remaining_specs_count} 個規格，商品主記錄及圖片將被保留。\n";
    }

    $pdo->commit();
    $output['success'] = true;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $output['error'] = '操作失敗：' . $e->getMessage();
    $output['success'] = false;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
exit;
