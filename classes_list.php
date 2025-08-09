<?php
session_start();

if (isset($_SESSION['member'])&&($_SESSION['member']['role'] === 'admin')) {
  include __DIR__ . '/classes_list_admin.php';
} else {
  include __DIR__ . '/classes_list_no_admin.php';
}
?>