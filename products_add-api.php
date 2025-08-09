<?php
include __DIR__ . '/parts/init.php'; 

header('Content-Type: application/json');
$output = [
  'success' => false,
  'error' => '',
  'postData' => $_POST,
  'files' => $_FILES   
];

// --- 資料接收與清理 ---
// 商品主表 (products) 相關欄位
$product_name = trim($_POST['product_name'] ?? '');
$brand_id  = intval($_POST['brand'] ?? 0);
$sport_id  = intval($_POST['sport'] ?? 0);

// 規格表 (specs) 相關欄位
$price  = intval($_POST['price'] ?? 0);   
$stock  = intval($_POST['stock'] ?? 0);
$material = trim($_POST['material'] ?? '');
$size_str = trim($_POST['size'] ?? '');
$weight = floatval($_POST['weight'] ?? 0); 
$color = trim($_POST['color'] ?? '');
$origin = trim($_POST['origin'] ?? '');

// --- 後端驗證 ---
if (empty($product_name)) {
  $output['error'] = '請填入商品名稱';
  echo json_encode($output);
  exit;
}
if (empty($sport_id)) {
  $output['error'] = '請選擇運動種類';
  echo json_encode($output);
  exit;
}
if (empty($brand_id)) {
  $output['error'] = '請選擇品牌';
  echo json_encode($output);
  exit;
}

// 價格驗證 (正整數)
if (!isset($_POST['price']) || !is_numeric($_POST['price'])) {
  $output['error'] = '單價格式不正確';
  echo json_encode($output);
  exit;
}
$price_check = intval($_POST['price']);
if ($price_check <= 0) {
  $output['error'] = '請填入有效的單價 (必須是正整數)';
  echo json_encode($output);
  exit;
}
// $price 已在上方用 intval() 處理

if ($stock < 0) {
  $output['error'] = '請填入有效的數量 (不可為負數)';
  echo json_encode($output);
  exit;
}
if (empty($material)) {
  $output['error'] = '請填入材質';
  echo json_encode($output);
  exit;
}
if (empty($size_str)) {
  $output['error'] = '請填入尺寸';
  echo json_encode($output);
  exit;
}
// 重量驗證 (如果是必填且必須大於0)
if (empty($_POST['weight'])) {
  $output['error'] = '請填入重量';
  echo json_encode($output);
  exit;
} elseif ($weight <= 0) {
  $output['error'] = '請填入有效的重量 (必須大於0)';
  echo json_encode($output);
  exit;
}

if (empty($color)) {
  $output['error'] = '請填入款式(顏色)';
  echo json_encode($output);
  exit;
}
if (empty($origin)) {
  $output['error'] = '請填入產地';
  echo json_encode($output);
  exit;
}

// 檢查是否有上傳圖片 (檢查第一個檔案的名稱是否為空，以及錯誤代碼)
if (empty($_FILES['productImages']['name'][0]) || $_FILES['productImages']['error'][0] == UPLOAD_ERR_NO_FILE) {
  $output['error'] = '請至少上傳一張圖片';
  echo json_encode($output);
  exit;
}

// --- 資料庫操作與檔案上傳 ---
try {
  $pdo->beginTransaction(); 

  // 1. 新增商品主資料到 products 表
  $stmt_product = $pdo->prepare("INSERT INTO products (name, brand_id, sport_id) VALUES (?, ?, ?)");
  $stmt_product->execute([$product_name, $brand_id, $sport_id]);
  $new_product_id = $pdo->lastInsertId(); // 取得新商品的 product_id

  if (!$new_product_id) {
    throw new Exception("無法新增商品主資料 (可能是資料庫錯誤或未返回 ID)。");
  }

  // 2. 新增規格資料到 specs 表 (使用上面已處理好的 $price)
  $stmt_spec = $pdo->prepare("INSERT INTO specs (product_id, price, color, stock, material, size, weight, origin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt_spec->execute([$new_product_id, $price, $color, $stock, $material, $size_str, $weight, $origin]);

  if ($stmt_spec->rowCount() == 0) {
    throw new Exception("無法新增商品規格資料。");
  }

  // 3. 處理圖片上傳並儲存到 images 表
  $image_order_counter = 1; // 新增商品的第一批圖片，順序從1開始
  $target_dir = __DIR__ . '/db/product_images/'; // 圖片儲存目錄

  // 檢查並建立圖片目錄
  if (!is_dir($target_dir)) {
    // 遞迴建立目錄，並設定權限
    if (!mkdir($target_dir, 0775, true) && !is_dir($target_dir)) { 
      throw new Exception(sprintf('錯誤：無法建立圖片目錄 "%s"。請檢查父目錄權限或手動建立。', $target_dir));
    }
  }
  // 檢查目錄是否可寫
  if (!is_writable($target_dir)) {
    throw new Exception(sprintf('錯誤：圖片目錄 "%s" 不可寫入。請檢查權限。', $target_dir));
  }

  // 處理上傳的每個檔案
  if (!empty($_FILES['productImages']) && is_array($_FILES['productImages']['tmp_name'])) {
    foreach ($_FILES['productImages']['tmp_name'] as $i => $tmp_name) {
      if ($_FILES['productImages']['error'][$i] == UPLOAD_ERR_OK) {
        if (!is_uploaded_file($tmp_name)) {
          $output['error'] .= "檔案 '" . ($_FILES['productImages']['name'][$i] ?? '未知') . "' 不是合法的上傳檔案。\n";
          continue; 
        }

        $original_filename = $_FILES['productImages']['name'][$i];
        $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif']; // 允許的副檔名

        if (!in_array($ext, $allowed_extensions)) {
          $output['error'] .= "檔案 '$original_filename' 的格式 ($ext) 不被允許 (只允許 jpg, jpeg, png, gif)。\n";
          continue; 
        }

        // 生成唯一檔名，只儲存檔名部分
        $filename = uniqid('img_', true) . '_' . time() . '.' . $ext;
        $target_path = $target_dir . $filename;

        // 移動上傳的檔案到目標位置
        if (move_uploaded_file($tmp_name, $target_path)) {
          // 檔案移動成功，將記錄寫入資料庫
          $stmt_image = $pdo->prepare("INSERT INTO images (product_id, image_url, image_order) VALUES (?, ?, ?)");
          $stmt_image->execute([$new_product_id, $filename, $image_order_counter]);
          $image_order_counter++;
        } else {
          // 檔案移動失敗，記錄錯誤
          // 根據 PHP 版本和設定，move_uploaded_file 可能因權限、路徑、空間不足等原因失敗
          $upload_error_message = error_get_last()['message'] ?? '未知原因';
          $output['error'] .= "檔案 '$original_filename' 上傳移動失敗。伺服器訊息: $upload_error_message\n";
        }
      } elseif ($_FILES['productImages']['error'][$i] != UPLOAD_ERR_NO_FILE) {
        // 如果不是「沒有檔案被上傳」的錯誤，則記錄其他上傳錯誤
        $output['error'] .= "檔案 '" . ($_FILES['productImages']['name'][$i] ?? '未知檔案') . "' 上傳時發生錯誤，代碼: " . $_FILES['productImages']['error'][$i] . "。請參考 PHP 檔案上傳錯誤代碼。\n";
      }
    }
  }

  // 檢查在圖片處理過程中是否有錯誤累積
  if (!empty($output['error'])) {
    throw new Exception("圖片處理過程中發生錯誤：\n" . $output['error']);
  }

  $pdo->commit(); 
  $output['success'] = true;
  $output['message'] = '商品及規格新增成功。';

} catch (Exception $e) {
  if ($pdo->inTransaction()) { 
    $pdo->rollBack(); // 
  }
  $output['error'] = (!empty($output['error']) ? $output['error'] . "\n" : '') . '資料庫操作或檔案處理失敗：' . $e->getMessage();
  $output['success'] = false; 
}


echo json_encode($output);
exit; 
