<?php
/**
 * Shared gallery admin authentication helpers.
 */

declare(strict_types=1);

const GALLERY_ADMIN_EMAIL = 'mugishaalex541@gmail.com';
const GALLERY_ADMIN_PASSWORD = 'Alex@541';
const GALLERY_ADMIN_COOKIE = 'nyakabingo_gallery_admin';
const GALLERY_ADMIN_COOKIE_DAYS = 30;
const GALLERY_ADMIN_AUTO_LOGIN = false;
const GALLERY_ADMIN_REMEMBER_LOGIN = false;

function gallery_admin_bootstrap(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Local admin mode: always keep admin authenticated whenever site is opened.
    if (GALLERY_ADMIN_AUTO_LOGIN) {
        $_SESSION['gallery_admin_authenticated'] = true;
        $_SESSION['gallery_admin_email'] = GALLERY_ADMIN_EMAIL;
        if (empty($_COOKIE[GALLERY_ADMIN_COOKIE])) {
            gallery_admin_set_cookie(gallery_admin_token());
        }
        return;
    }

    if (gallery_is_admin()) {
        return;
    }

    if (!GALLERY_ADMIN_REMEMBER_LOGIN) {
        gallery_admin_clear_cookie();
        return;
    }

    $cookieToken = $_COOKIE[GALLERY_ADMIN_COOKIE] ?? '';
    if (!is_string($cookieToken) || $cookieToken === '') {
        return;
    }

    if (!hash_equals(gallery_admin_token(), $cookieToken)) {
        gallery_admin_clear_cookie();
        return;
    }

    $_SESSION['gallery_admin_authenticated'] = true;
    $_SESSION['gallery_admin_email'] = GALLERY_ADMIN_EMAIL;
}

function gallery_admin_token(): string
{
    return hash('sha256', GALLERY_ADMIN_EMAIL . '|' . GALLERY_ADMIN_PASSWORD . '|NYAKABINGO_PRIMARY');
}

function gallery_is_admin(): bool
{
    return !empty($_SESSION['gallery_admin_authenticated'])
        && (($_SESSION['gallery_admin_email'] ?? '') === GALLERY_ADMIN_EMAIL);
}

function gallery_admin_login(string $email, string $password): bool
{
    if ($email !== GALLERY_ADMIN_EMAIL || $password !== GALLERY_ADMIN_PASSWORD) {
        return false;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['gallery_admin_authenticated'] = true;
    $_SESSION['gallery_admin_email'] = GALLERY_ADMIN_EMAIL;

    if (GALLERY_ADMIN_REMEMBER_LOGIN) {
        gallery_admin_set_cookie(gallery_admin_token());
    }
    return true;
}

function gallery_admin_logout(): void
{
    unset($_SESSION['gallery_admin_authenticated'], $_SESSION['gallery_admin_email']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    gallery_admin_clear_cookie();
}

function gallery_admin_set_cookie(string $value): void
{
    $expires = time() + (GALLERY_ADMIN_COOKIE_DAYS * 24 * 60 * 60);
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    setcookie(
        GALLERY_ADMIN_COOKIE,
        $value,
        [
            'expires' => $expires,
            'path' => '/NYAKABINGO_PRIMARY/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

function gallery_admin_clear_cookie(): void
{
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    setcookie(
        GALLERY_ADMIN_COOKIE,
        '',
        [
            'expires' => time() - 3600,
            'path' => '/NYAKABINGO_PRIMARY/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}
