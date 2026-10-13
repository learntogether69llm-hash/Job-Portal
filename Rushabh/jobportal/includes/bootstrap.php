<?php
declare(strict_types=1);

/**
 * includes/bootstrap.php
 * Loaded first by every page: config, database, session and small helpers.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

date_default_timezone_set('UTC'); // All datetimes in the database are UTC.

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// ---------- Session ----------
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', (string) (REMEMBER_ME_DAYS * 86400));

session_set_cookie_params([
    'lifetime' => 0, // Browser-session cookie. "Keep me signed in" extends it at login.
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ---------- Helpers ----------

/** Escape text for safe output in HTML. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function utc_now(): string
{
    return gmdate('Y-m-d H:i:s');
}

// ---------- CSRF ----------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------- Flash messages and form state ----------

/**
 * Store a message (and optional field errors / old input) for the next page load.
 * $errors is keyed by input id, e.g. ['signup-email' => 'That email is already registered.'].
 */
function flash(string $type, string $message, string $tab, array $errors = [], array $old = []): void
{
    $_SESSION['flash']  = ['type' => $type, 'message' => $message, 'tab' => $tab];
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = $old;
}

// ---------- Auth ----------

function current_user(): ?array
{
    static $user = false;

    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $stmt = db()->prepare(
                'SELECT id, name, email, mobile_number, role, status FROM users WHERE id = :id LIMIT 1'
            );
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $row = $stmt->fetch();
            if ($row && $row['status'] === 'active') {
                $user = $row;
            } else {
                unset($_SESSION['user_id']);
            }
        }
    }

    return $user;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        redirect('index.php');
    }
    return $user;
}

/** Log a user in: fresh session id, optional long-lived cookie, last_login_at update. */
function log_in_user(int $userId, bool $remember): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;

    if ($remember) {
        setcookie(session_name(), session_id(), [
            'expires'  => time() + REMEMBER_ME_DAYS * 86400,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    $stmt = db()->prepare('UPDATE users SET last_login_at = :now WHERE id = :id');
    $stmt->execute([':now' => utc_now(), ':id' => $userId]);
}

// ---------- Mobile numbers ----------

/**
 * Turn what someone typed into the stored format (+919876543210), or null if unusable.
 * Accepts "+91 98765 43210", "98765-43210" (uses DEFAULT_COUNTRY_CODE) or "919876543210".
 */
function normalize_mobile(string $input): ?string
{
    $input  = trim($input);
    $digits = preg_replace('/\D/', '', $input) ?? '';

    if ($digits === '') {
        return null;
    }

    if (str_starts_with($input, '+')) {
        $full = '+' . $digits;
    } elseif (strlen($digits) === 10) {
        $full = DEFAULT_COUNTRY_CODE . $digits;
    } else {
        $full = '+' . $digits;
    }

    $length = strlen($full) - 1; // digits only
    return ($length >= 8 && $length <= 15) ? $full : null;
}
