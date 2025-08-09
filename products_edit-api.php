<?php
include __DIR__ . '/parts/init.php';

$output = ['success' => false, 'error' => ''];

$spec_id = intval($_POST['spec_id'] ?? 0);
$product_id = intval($_POST['product_id'] ?? 0);
$product_name = $_POST['product_name'] ?? '';
$brand = intval($_POST['brand'] ?? 0);
$sport = intval($_POST['sport'] ?? 0);

$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$size = $_POST['size'] ?? '';
$weight = $_POST['weight'] ?? '';
$color = $_POST['color'] ?? '';
$origin = $_POST['origin'] ?? '';
$material = $_POST['material'] ?? '';

if (!$spec_id || !$product_id) {
    $output['error'] = '缺少必要參數';
    echo json_encode($output);
    exit;
}

try {
    $pdo->beginTransaction();

    // 商品情報の更新
    $pdo->prepare("UPDATE products SET name=?, brand_id=?, sport_id=? WHERE product_id=?")
        ->execute([$product_name, $brand, $sport, $product_id]);

    $pdo->prepare("UPDATE specs SET price=?, stock=?, size=?, weight=?, color=?, origin=?, material=? WHERE spec_id=?")
        ->execute([$price, $stock, $size, $weight, $color, $origin, $material, $spec_id]);

    // 削除された画像の処理
    $deleted_ids = $_POST['deleted_ids'] ?? [];
    if (!empty($deleted_ids)) {
        $ids = array_map('intval', $deleted_ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("SELECT image_url FROM images WHERE image_id IN ($placeholders)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();

        foreach ($rows as $r) {
            $filename = $r['image_url'];
            $filepath = __DIR__ . '/db/product_images/' . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        $deleteStmt = $pdo->prepare("DELETE FROM images WHERE image_id IN ($placeholders)");
        $deleteStmt->execute($ids);
    }

    // 画像アップロード処理
    $folder = 'db/product_images/';
    $path = __DIR__ . '/' . $folder;

    if (!is_dir($path)) {
        if (!mkdir($path, 0755, true)) {
            throw new Exception('圖片保存失敗');
        }
    }

    if (isset($_FILES['productImages']) && is_array($_FILES['productImages']['tmp_name'])) {
        foreach ($_FILES['productImages']['tmp_name'] as $i => $tmp_name) {
            if ($_FILES['productImages']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            if (!is_uploaded_file($tmp_name)) {
                continue;
            }

            $original_name = $_FILES['productImages']['name'][$i] ?? '';
            if (empty($original_name)) {
                continue;
            }

            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed_extensions)) {
                continue;
            }

            $newName = uniqid('img_', true) . '.' . $ext;
            $full_path = $path . $newName;

            if (move_uploaded_file($tmp_name, $full_path)) {
                $stmt = $pdo->prepare("SELECT COALESCE(MAX(image_order), 0) as max_order FROM images WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $max_order = $stmt->fetch()['max_order'];

                $insert_stmt = $pdo->prepare("INSERT INTO images (product_id, image_url, image_order) VALUES (?, ?, ?)");
                $insert_stmt->execute([$product_id, $newName, $max_order + 1]);
            }
        }
    }

    $pdo->commit();
    $output['success'] = true;
} catch (Exception $e) {
    $pdo->rollBack();
    $output['error'] = '系統發生問題';

    // エラーログに記録（本番環境では推奨）
    error_log("Product update error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}

echo json_encode($output);
