<?php
require_once __DIR__ . '/core/env.php';
require_once __DIR__ . '/core/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new Auth();
$envRedirect = getenv('GOOGLE_REDIRECT_URI') ?: null;
// Provide a debug URL if requested and APP_DEBUG is enabled
if ((getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? null)) == '1' && isset($_GET['debug'])) {
    $clientId = getenv('GOOGLE_CLIENT_ID');
    $scope = 'openid email profile';
    $params = http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $envRedirect,
        'response_type' => 'code',
        'scope' => $scope,
        'access_type' => 'offline',
        'prompt' => 'select_account'
    ]);
    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    echo '<h2>DEBUG - Google OAuth URL</h2>';
    echo '<pre>' . htmlspecialchars($url) . '</pre>';
    exit();
}
// Trigger SSO flow - ssoLogin will redirect to Google if no code parameter
$result = $auth->ssoLogin('google', $envRedirect);
if ($result === false) {
    http_response_code(500);
    echo '<h2>SSO Error</h2>';
    echo '<p>Google SSO misconfigured or disabled on the server.</p>';
    exit();
}
// If $auth->ssoLogin didn't exit and returned true, the user should be logged in.
// Redirect to home/dashboard
header('Location: /eventsphere/dashboard.php'); exit();
?>
