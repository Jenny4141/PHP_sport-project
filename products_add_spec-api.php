<?php
include __DIR__ . '/parts/init.php';

header('Content-Type: application/json');
$output = [
    'success' => false,
    'error' => '',
    'postData' => $_POST
];

$product_id = intval($_POST['product_id'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$material = trim($_POST['material'] ?? '');
$size = trim($_POST['size'] ?? '');
$weight = floatval($_POST['weight'] ?? 0); 
$color = trim($_POST['color'] ?? '');
$origin = trim($_POST['origin'] ?? '');


if (!$product_id || !$price || !$material || !$size || !$weight || !$color || !$origin) {
    $output['error'] = '欄位資料不完整';
    echo json_encode($output);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO specs (product_id, price, color, stock, material, size, weight, origin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$product_id, $price, $color, $stock, $material, $size, $weight, $origin]);

    // 圖片處理
    if (!empty($_FILES['productImages']) && is_array($_FILES['productImages']['tmp_name'])) {
        $stmt_max_order = $pdo->prepare("SELECT MAX(image_order) FROM images WHERE product_id = ?");
        $stmt_max_order->execute([$product_id]);
        $max_order = $stmt_max_order->fetchColumn();
        $image_order_counter = $max_order ? $max_order + 1 : 1; 

        foreach ($_FILES['productImages']['tmp_name'] as $i => $tmp_name) {
            if (is_uploaded_file($tmp_name) && $_FILES['productImages']['error'][$i] == UPLOAD_ERR_OK) {
                $original_filename = $_FILES['productImages']['name'][$i];
                $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($ext, $allowed_extensions)) continue;

                $filename = uniqid('spec_img_') . '_' . time() . '.' . $ext;
                $target_dir = __DIR__ . '/db/product_images/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $target_path = $target_dir . $filename;

                if (move_uploaded_file($tmp_name, $target_path)) {
                    $image_url_to_db = $filename; // 修改後：只儲存檔名
                    $stmt_image = $pdo->prepare("INSERT INTO images (product_id, image_url, image_order) VALUES (?, ?, ?)");
                    $stmt_image->execute([$product_id, $image_url_to_db, $image_order_counter]);
                    $image_order_counter++;
                }
            }
        }
    }

    $pdo->commit();
    $output['success'] = true;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $output['error'] = '發生錯誤：' . $e->getMessage();
}

echo json_encode($output);
exit;
