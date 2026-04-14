<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

function requireAuth(): array {
    if (empty($_SESSION['user'])) {
        header('Location: /auth/login.php');
        exit;
    }
    return $_SESSION['user'];
}

function requireRole(string ...$roles): array {
    $user = requireAuth();
    if (!in_array($user['role'], $roles)) {
        header('Location: /pages/dashboard.php');
        exit;
    }
    return $user;
}

function requireRoleAPI(string ...$roles): array {
    $user = requireAuth();
    if (!in_array($user['role'], $roles)) {
        throw new Exception('Access denied - required role: ' . implode(' or ', $roles));
    }
    return $user;
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}
