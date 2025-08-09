<?php

session_start();

unset($_SESSION['member']);

$come_from = "index_.php";
if (! empty($_SERVER['HTTP_REFERER'])) {
  $come_from = $_SERVER['HTTP_REFERER'];
}

header("Location: $come_from");