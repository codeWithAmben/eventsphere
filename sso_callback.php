<?php
require_once __DIR__ . '/core/env.php';
require_once __DIR__ . '/core/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new Auth();
$envRedirect = getenv('GOOGLE_REDIRECT_URI') ?: null;
$ok = $auth->ssoLogin('google', $envRedirect);
if ($ok === false) {
    http_response_code(400);
    echo '<h2>SSO callback error</h2>';
    echo '<p>There was an error handling the SSO callback.</p>';
    exit();
}
// On success, redirect based on role
$role = strtolower(trim($_SESSION['role'] ?? 'attendee'));
if ($role === 'admin') header('Location: /eventsphere/admin.php?tab=overview');
else header('Location: /eventsphere/dashboard.php');
exit();
?>
