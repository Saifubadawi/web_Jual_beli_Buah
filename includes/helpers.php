<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $base = '/Web_Fap';
    return $base . '/' . ltrim($path, '/');
}

function format_rupiah(int $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}

function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

function verify_csrf(?string $token): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!$token || !hash_equals((string)$expected, (string)$token)) {
        http_response_code(403);
        echo 'CSRF token tidak valid.';
        exit;
    }
}
