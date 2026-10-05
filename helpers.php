<?php
declare(strict_types=1);

// Start a secure session once so pages can identify the signed-in user.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function e(?string $value): string
{
    // Escape text before showing it in HTML to prevent script injection.
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function current_user_id(): ?int
{
    // Return the logged-in user's ID, or null for a guest.
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function require_login(): void
{
    // Private pages send guests to the login form.
    if (current_user_id() === null) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string
{
    // Reuse one random token for this session's forms.
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    // Reject a form if its token is missing or does not match the session.
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Invalid form token. Please go back and try again.');
    }
}

function flash(string $message, string $type = 'success'): void
{
    // Save a short message to show after the next page load.
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    // Read the saved message once, then clear it.
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
