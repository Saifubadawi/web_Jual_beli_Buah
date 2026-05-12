<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . base_url('/login.php'));
        exit;
    }
}

function require_role(string $role): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        echo 'Akses ditolak.';
        exit;
    }
}
