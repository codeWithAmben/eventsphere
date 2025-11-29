<?php
/**
 * auth.php - Authentication helper (demo skeleton)
 * - Uses the XML store for sample logins
 * - In production, replace with a robust DB + secure hashing and SSO provider
 */

require_once __DIR__ . '/xml_handler.php';

class Auth {
    private $usersFile;

    public function __construct($usersFile = __DIR__ . '/../data/users.xml') {
        $this->usersFile = $usersFile;
    }

    // Find or create a user in data/users.xml by email address
    public function findOrCreateUserByEmail($email, $profile = []) {
        $xh = new XmlHandler($this->usersFile);
        $xml = $xh->read();
        if (!$xml) $xml = new SimpleXMLElement('<users></users>');
        // find existing by email
        foreach ($xml->user as $u) {
            if (strtolower((string)$u->email) === strtolower($email)) return $u;
        }
        // create a new user
        $maxId = 0; foreach ($xml->user as $u) { $id = (int)$u['id']; if ($id > $maxId) $maxId = $id; }
        $id = $maxId + 1;
        $u = $xml->addChild('user'); $u->addAttribute('id', $id);
        $u->addChild('username', htmlspecialchars($profile['name'] ?? $profile['given_name'] ?? 'sso_' . $id));
        $u->addChild('email', htmlspecialchars($email));
        $u->addChild('password', password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT));
        $u->addChild('role', 'attendee');
        $xh->saveSimpleXML($xml);
        return $u;
    }

    public function login($username, $password) {
        $xh = new XmlHandler($this->usersFile);
        $xml = $xh->read();
        if (!$xml) return false;
        foreach ($xml->user as $user) {
            if ((string)$user->username === $username) {
                // This demo expects hashed passwords
                if (password_verify($password, (string)$user->password)) {
                    // Set session
                    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                    $_SESSION['user_id'] = (string)$user['id'];
                    $_SESSION['username'] = (string)$user->username;
                    return true;
                }
            }
        }
        return false;
    }

    public function logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        session_unset();
        session_destroy();
    }

    public function requireLogin() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventsphere/login.php');
            exit();
        }
        return true;
    }

    // SSO using OAuth2 (Google example). Requires GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in env.
    public function ssoLogin($provider = 'google', $redirectUri = null) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $provider = strtolower($provider);

        if ($provider !== 'google') {
            // Only Google implemented in this helper
            return false;
        }

        $clientId = getenv('GOOGLE_CLIENT_ID');
        $clientSecret = getenv('GOOGLE_CLIENT_SECRET');
        if (!$clientId || !$clientSecret) return false;

        // Prefer configured redirect in env to avoid host/port mismatches during dev.
        $envRedirect = getenv('GOOGLE_REDIRECT_URI') ?: ($_ENV['GOOGLE_REDIRECT_URI'] ?? '');
        if ($envRedirect && trim($envRedirect) !== '') {
            $redirectUri = trim($envRedirect);
        }
        // Build a sensible default redirect URI if none provided (script path without query)
        if (!$redirectUri) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $redirectUri = $scheme . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
        }

        // Step 1: if no "code" param, redirect user to Google's consent page
        if (!isset($_GET['code'])) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth2state'] = $state;

            $params = [
                'client_id' => $clientId,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'redirect_uri' => $redirectUri,
                'state' => $state,
                'access_type' => 'offline',
                'prompt' => 'select_account'
            ];
            $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
            header('Location: ' . $authUrl);
            exit();
        }

        // Step 2: callback - validate state
        if (!isset($_GET['state']) || !isset($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
            unset($_SESSION['oauth2state']);
            return false;
        }
        unset($_SESSION['oauth2state']);

        // Exchange code for tokens
        $code = $_GET['code'];
        $tokenEndpoint = 'https://oauth2.googleapis.com/token';
        $post = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $tokenResp = $this->httpPostForm($tokenEndpoint, $post);
        if (!$tokenResp || empty($tokenResp['access_token'])) return false;

        // Fetch user info
        $accessToken = $tokenResp['access_token'];
        $userInfo = $this->httpGetJson('https://openidconnect.googleapis.com/v1/userinfo', ['Authorization: Bearer ' . $accessToken]);
        if (!$userInfo || empty($userInfo['sub'])) return false;

        // Ensure we have an internal user entry (create if needed) and map session to it
        $email = $userInfo['email'] ?? null;
        if ($email) {
            $internalUser = $this->findOrCreateUserByEmail($email, $userInfo);
            if ($internalUser) {
                $_SESSION['user_id'] = (string)$internalUser['id'];
                $_SESSION['username'] = (string)$internalUser->username;
                $_SESSION['role'] = (string)$internalUser->role;
            } else {
                // fallback
                $_SESSION['user_id'] = $userInfo['sub'] ?? ($userInfo['email'] ?? null);
                $_SESSION['username'] = $userInfo['name'] ?? ($userInfo['email'] ?? 'sso_user');
            }
        } else {
            $_SESSION['user_id'] = $userInfo['sub'] ?? ($userInfo['email'] ?? null);
            $_SESSION['username'] = $userInfo['name'] ?? ($userInfo['email'] ?? 'sso_user');
        }

        return true;
    }

    // Simple HTTP helpers
    private function httpPostForm($url, array $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($resp === false || $err) return null;
        $json = json_decode($resp, true);
        return $json ?: null;
    }

    private function httpGetJson($url, array $headers = []) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($resp === false || $err) return null;
        $json = json_decode($resp, true);
        return $json ?: null;
    }
    
}

?>