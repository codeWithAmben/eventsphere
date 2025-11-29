<?php
/**
 * csrf.php - simple CSRF token helper
 * Stores a per-session CSRF token in $_SESSION['csrf_token'].
 * Use csrf_input() to render a hidden form input, and csrf_validate() to check.
 */

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function csrf_get_token() {
    if (!isset($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
        } catch (Exception $ex) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(24));
        }
    }
    return $_SESSION['csrf_token'];
}

function csrf_input_field() {
    $t = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $t . '" />';
}

function csrf_validate($token) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!isset($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

?>
