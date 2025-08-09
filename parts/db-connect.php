<?php

require __DIR__. '/db-config.php';

$dsn = sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4;", DB_HOST, DB_NAME, DB_PORT);

$pdo_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // 錯誤訊息使用例外方式
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 預設 fetch 時取得關聯式陣列
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdo_options);
    // echo "✅ 資料庫連線成功！";
} catch (PDOException $e) {
    echo "❌ 資料庫連線失敗：" . $e->getMessage();
}
