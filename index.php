<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: /auth/login.php'); exit; }
header('Location: /pages/dashboard.php'); exit;
