<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'message' => '',
  'postData' => $_POST,
  'orderId' => null,
];

$member_id_input = trim($_POST['member_id'] ?? '');
$delivery_method = trim($_POST['delivery'] ?? '');
$shipping_fee_input = $_POST['fee'] ?? null;
$payment_method = trim($_POST['payment'] ?? '');
$shipping_address = trim($_POST['address'] ?? '');
$invoice_type = trim($_POST['invoice'] ?? '');
$order_status = trim($_POST['status'] ?? '');
$new_items_input = $_POST['new_items'] ?? [];
$errors = [];

if (empty($member_id_input)) {
  $errors['member_id'] = '會員ID為必填';
} elseif (!ctype_digit($member_id_input) || $member_id_input === '0' || str_starts_with($member_id_input, '-')) {
  $errors['member_id'] = '會員ID必須為有效的正整數';
} else {
  $member_id = $member_id_input;
}

if (empty($delivery_method)) {
  $errors['delivery'] = '物流方式為必填';
}

if ($shipping_fee_input === null || $shipping_fee_input === '' || !is_numeric($shipping_fee_input) || (int)$shipping_fee_input < 0 || strpos($shipping_fee_input, '.') !== false) {
  $errors['fee'] = '運費為必填的非負整數';
} else {
  $shipping_fee = (int)$shipping_fee_input;
}

if (empty($payment_method)) {
  $errors['payment'] = '付款方式為必填';
}
if (empty($shipping_address)) {
  $errors['address'] = '住址為必填';
}
if (empty($invoice_type)) {
  $errors['invoice'] = '發票類型為必填';
}
if (empty($order_status)) {
  $errors['status'] = '訂單狀態為必填';
}

$items_subtotal_calculated = 0;
if (empty($new_items_input) || !is_array($new_items_input)) {
  $errors['items'] = '訂單至少需要一個商品項目';
} else {
  foreach ($new_items_input as $key => $item) {
    if (empty($item['spec_id'])) {
      $errors["new_items[$key][spec_id]"] = '款式選擇不完整 (spec_id)';
    }
    if (!isset($item['quantity']) || !ctype_digit((string)$item['quantity']) || (int)$item['quantity'] < 1) {
      $errors["new_items[$key][quantity]"] = '商品數量必須為至少1的整數';
    }
    if (!isset($item['price_at_order']) || !is_numeric($item['price_at_order']) || (int)round(floatval($item['price_at_order'])) < 0) {
      $errors["new_items[$key][price_at_order]"] = '商品單價必須為非負數';
    } else {
      $items_subtotal_calculated += (intval(round(floatval($item['price_at_order']))) * intval($item['quantity']));
    }
  }
}

if (!empty($errors)) {
  $output['error'] = '資料驗證失敗';
  $output['errorsDetail'] = $errors;
  $output['message'] = implode('; ', array_values($errors));
  echo json_encode($output, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $pdo->beginTransaction();

  $sql_order = "INSERT INTO orders (
                      member_id, total, fee, address,
                      status, payment, invoice, delivery,
                      created
                  ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, NOW() )";
  $stmt_order = $pdo->prepare($sql_order);
  $stmt_order->execute([
    $member_id,
    $items_subtotal_calculated,
    $shipping_fee,
    $shipping_address,
    $order_status,
    $payment_method,
    $invoice_type,
    $delivery_method,
  ]);

  $new_order_id = $pdo->lastInsertId();

  if (!$new_order_id) {
    throw new Exception("無法新增訂單主資料。");
  }
  $output['orderId'] = $new_order_id;

  $sql_fetch_snapshot_data = "SELECT
                                  p.name AS product_name,
                                  sp.color AS spec_color,
                                  sp.price AS spec_current_price,
                                  (SELECT img.image_url FROM images img WHERE img.product_id = sp.product_id ORDER BY img.image_order ASC LIMIT 1) AS product_image
                              FROM specs sp
                              JOIN products p ON sp.product_id = p.product_id
                              WHERE sp.spec_id = :spec_id";
  $stmt_fetch_snapshot = $pdo->prepare($sql_fetch_snapshot_data);

  $sql_insert_item = "INSERT INTO order_items (
                          order_id, spec_id, quantity, price,
                          ordered_product_name, ordered_spec_color,
                          ordered_product_image, item_status
                      ) VALUES (
                          :order_id, :spec_id, :quantity, :price,
                          :p_name, :s_color,
                          :p_img, 'active'
                      )";
  $stmt_item = $pdo->prepare($sql_insert_item);

  foreach ($new_items_input as $item_data) {
    $current_spec_id = intval($item_data['spec_id']);
    $current_quantity = intval($item_data['quantity']);

    $stmt_fetch_snapshot->execute(['spec_id' => $current_spec_id]);
    $snapshot_data = $stmt_fetch_snapshot->fetch(PDO::FETCH_ASSOC);

    if (!$snapshot_data) {
      throw new Exception("無法獲取規格ID {$current_spec_id} 的快照資訊。");
    }
    $price_for_new_item = intval(round(floatval($snapshot_data['spec_current_price'])));


    $stmt_item->execute([
      'order_id' => $new_order_id,
      'spec_id' => $current_spec_id,
      'quantity' => $current_quantity,
      'price' => $price_for_new_item,
      'p_name' => $snapshot_data['product_name'],
      's_color' => $snapshot_data['spec_color'],
      'p_img' => $snapshot_data['product_image']
    ]);
    if ($stmt_item->rowCount() == 0) {
      throw new Exception("無法新增訂單項目 (Spec ID: {$current_spec_id})。");
    }
  }

  $pdo->commit();
  $output['success'] = true;
  $output['message'] = '訂單已成功建立！訂單編號: ' . $new_order_id;
} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  $output['error'] = '資料庫操作失敗';
  $output['message'] = $e->getMessage();
  $output['success'] = false;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
exit;
