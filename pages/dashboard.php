<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireAuth();

if (in_array($user['role'], ['admin', 'manager'])) {
    header('Location: /pages/ringkasan.php');
} else {
    header('Location: /pages/kalkulator.php');
}
exit;
