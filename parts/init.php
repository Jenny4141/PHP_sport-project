<?php
if (!isset($_SESSION)) {
   # 尚未初始化 session 的話，就啟用
  session_start();
}
if (!isset($_SESSION['member'])) {
    // 避免 redirect loop
    if (!in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'login-api.php'])) {
        header('Location: login.php');
        exit;
    }
}
require __DIR__ . '/db-connect.php';
$imageBasePath = dirname($_SERVER['PHP_SELF']) . '/db/product_images/';