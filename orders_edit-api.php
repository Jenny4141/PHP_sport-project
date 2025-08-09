<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
    'success' => false,
    'error' => '',
    'message' => '',
    'order_id' => 0,
];

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
if (empty($order_id)) {
    $output['error'] = '缺少訂單ID (order_id)';
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}
$output['order_id'] = $order_id;

$pdo->beginTransaction();

try {
    $fee = isset($_POST['fee']) ? intval($_POST['fee']) : 0;
    $delivery = $_POST['delivery'] ?? '';
    $payment = $_POST['payment'] ?? '';
    $address = $_POST['address'] ?? '';
    $invoice = $_POST['invoice'] ?? '';
    $status = $_POST['status'] ?? '';

    $sql_update_order = "UPDATE `orders` SET
                            `fee`=?, `delivery`=?, `payment`=?, `address`=?,
                            `invoice`=?, `status`=?
                        WHERE `order_id`=?";
    $stmt_update_order = $pdo->prepare($sql_update_order);
    $stmt_update_order->execute([
        $fee,
        $delivery,
        $payment,
        $address,
        $invoice,
        $status,
        $order_id
    ]);

    if (isset($_POST['deleted_item_ids']) && is_array($_POST['deleted_item_ids'])) {
        $sql_delete_item = "DELETE FROM `order_items` WHERE `item_id` = ? AND `order_id` = ?";
        $stmt_delete_item = $pdo->prepare($sql_delete_item);
        foreach ($_POST['deleted_item_ids'] as $item_id_to_delete) {
            if (intval($item_id_to_delete) > 0) {
                $stmt_delete_item->execute([intval($item_id_to_delete), $order_id]);
            }
        }
    }

    $sql_fetch_spec_snapshot_data = "SELECT
                                        p.name AS product_name,
                                        sp.color AS spec_color,
                                        sp.price AS spec_current_price,
                                        (SELECT img.image_url FROM images img WHERE img.product_id = sp.product_id ORDER BY img.image_order ASC LIMIT 1) AS product_image
                                    FROM specs sp
                                    JOIN products p ON sp.product_id = p.product_id
                                    WHERE sp.spec_id = :spec_id";
    $stmt_fetch_spec_snapshot = $pdo->prepare($sql_fetch_spec_snapshot_data);

    if (isset($_POST['items']) && is_array($_POST['items'])) {
        $sql_get_original_item_data = "SELECT spec_id, price, ordered_product_name, ordered_spec_color, ordered_product_image, item_status FROM order_items WHERE item_id = ? AND order_id = ?";
        $stmt_get_original_item_data = $pdo->prepare($sql_get_original_item_data);

        $sql_update_existing_item = "UPDATE `order_items` SET
                                        `spec_id`=?, `quantity`=?, `price`=?,
                                        `ordered_product_name`=?, `ordered_spec_color`=?,
                                        `ordered_product_image`=?, `item_status`=?
                                    WHERE `item_id`=? AND `order_id`=?";
        $stmt_update_existing_item = $pdo->prepare($sql_update_existing_item);

        foreach ($_POST['items'] as $item_id_str => $item_data) {
            $current_item_id = intval($item_id_str);
            if ($current_item_id <= 0) continue;

            $posted_spec_id = isset($item_data['spec_id']) ? intval($item_data['spec_id']) : 0;
            $posted_quantity = isset($item_data['quantity']) ? intval($item_data['quantity']) : 0;

            if ($posted_quantity <= 0) {
                $output['error'] .= "訂單項目ID {$current_item_id} 的數量必須大於0。 ";
                continue;
            }
            if ($posted_spec_id <= 0 && array_key_exists('spec_id', $item_data)) { // 只有當 spec_id 被提交且無效時才報錯
                $output['error'] .= "訂單項目ID {$current_item_id} 提交的規格ID無效。 ";
                continue;
            }


            $stmt_get_original_item_data->execute([$current_item_id, $order_id]);
            $original_item = $stmt_get_original_item_data->fetch(PDO::FETCH_ASSOC);

            if (!$original_item) {
                $output['error'] .= "找不到訂單項目ID {$current_item_id} 的原始資料進行更新。 ";
                continue;
            }

            $final_spec_id_to_update = $original_item['spec_id'];
            $final_price_to_update = $original_item['price'];
            $final_ordered_product_name = $original_item['ordered_product_name'];
            $final_ordered_spec_color = $original_item['ordered_spec_color'];
            $final_ordered_product_image = $original_item['ordered_product_image'];
            $final_item_status = $original_item['item_status'];


            if ($posted_spec_id > 0 && $posted_spec_id != $original_item['spec_id']) {
                $stmt_fetch_spec_snapshot->execute(['spec_id' => $posted_spec_id]);
                $new_spec_details = $stmt_fetch_spec_snapshot->fetch(PDO::FETCH_ASSOC);

                if (!$new_spec_details) {
                    throw new Exception("為訂單項目ID {$current_item_id} 選擇的新規格 (ID: {$posted_spec_id}) 不存在於規格表中。");
                }
                $final_spec_id_to_update = $posted_spec_id;
                $final_price_to_update = intval(round(floatval($new_spec_details['spec_current_price'])));
                $final_ordered_product_name = $new_spec_details['product_name'];
                $final_ordered_spec_color = $new_spec_details['spec_color'];
                $final_ordered_product_image = $new_spec_details['product_image'];
                $final_item_status = 'active';
            }

            $stmt_update_existing_item->execute([
                $final_spec_id_to_update,
                $posted_quantity,
                $final_price_to_update,
                $final_ordered_product_name,
                $final_ordered_spec_color,
                $final_ordered_product_image,
                $final_item_status,
                $current_item_id,
                $order_id
            ]);
        }
    }

    if (isset($_POST['new_items']) && is_array($_POST['new_items'])) {
        $sql_insert_new_item = "INSERT INTO `order_items`
                                (`order_id`, `spec_id`, `quantity`, `price`,
                                `ordered_product_name`, `ordered_spec_color`, `ordered_product_image`, `item_status`)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
        $stmt_insert_new_item = $pdo->prepare($sql_insert_new_item);

        foreach ($_POST['new_items'] as $key => $item_data) {
            $spec_id = isset($item_data['spec_id']) ? intval($item_data['spec_id']) : 0;
            $quantity = isset($item_data['quantity']) ? intval($item_data['quantity']) : 0;

            if ($spec_id > 0 && $quantity > 0) {
                $stmt_fetch_spec_snapshot->execute(['spec_id' => $spec_id]);
                $snapshot_data = $stmt_fetch_spec_snapshot->fetch(PDO::FETCH_ASSOC);
                if (!$snapshot_data) {
                    throw new Exception("新增項目時找不到規格ID {$spec_id} 的快照資訊。");
                }
                $price_for_new_item = intval(round(floatval($snapshot_data['spec_current_price'])));

                $stmt_insert_new_item->execute([
                    $order_id,
                    $spec_id,
                    $quantity,
                    $price_for_new_item,
                    $snapshot_data['product_name'],
                    $snapshot_data['spec_color'],
                    $snapshot_data['product_image']
                ]);
            }
        }
    }

    $sql_calc_total = "SELECT SUM(`price` * `quantity`) AS `calculated_total`
                       FROM `order_items`
                       WHERE `order_id` = ?";
    $stmt_calc_total = $pdo->prepare($sql_calc_total);
    $stmt_calc_total->execute([$order_id]);
    $calculated_row = $stmt_calc_total->fetch(PDO::FETCH_ASSOC);

    $new_order_total = 0;
    if ($calculated_row && $calculated_row['calculated_total'] !== null) {
        $new_order_total = floatval($calculated_row['calculated_total']);
    }
    $sql_update_order_total = "UPDATE `orders` SET `total` = ? WHERE `order_id` = ?";
    $stmt_update_order_total = $pdo->prepare($sql_update_order_total);
    $stmt_update_order_total->execute([intval(round($new_order_total)), $order_id]);

    if (!empty($output['error'])) {
        throw new Exception(trim($output['error']));
    }

    $pdo->commit();
    $output['success'] = true;
    $output['message'] = '訂單已成功更新！';
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $output['error'] .= '資料庫操作失敗：' . $e->getMessage();
    $output['success'] = false;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
