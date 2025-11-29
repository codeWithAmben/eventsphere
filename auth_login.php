<?php
// auth_login.php - smart login handling by email with role-based redirect
require_once __DIR__ . '/core/xml_handler.php';
require_once __DIR__ . '/core/csrf.php';

session_start();

// CSRF validation
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_validate($csrf)) {
    header('Location: /eventsphere/login.php?error=1');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /eventsphere/login.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    header('Location: /eventsphere/login.php?error=1');
    exit();
}

// Helper: create an xpath literal safely
function xpath_literal($s) {
    if (strpos($s, "'") === false) return "'" . $s . "'";
    // string contains single quote; create concat('a', "'", 'b') sequence
    $parts = explode("'", $s);
    $concatParts = [];
    $lastIndex = count($parts) - 1;
    foreach ($parts as $idx => $p) {
        // add the part string if not empty
        $concatParts[] = "'" . $p . "'";
        // if not the last, add a single-quote literal
        if ($idx !== $lastIndex) $concatParts[] = '"\'"';
    }
    return 'concat(' . implode(', ', $concatParts) . ')';
}

// Load users.xml
$xh = new XmlHandler(__DIR__ . '/data/users.xml');
$xml = $xh->read();

if (!$xml) {
    header('Location: /eventsphere/login.php?error=1');
    exit();
}

$user = null;
// First try an XPath search for case-sensitive match
if ($xml) {
    try {
        $lit = xpath_literal($email);
        // Try exact email match first
        $matches = $xml->xpath("//user[email={$lit}]");
        // If no matches, try case-insensitive match (lowercase compare)
        if (!$matches || count($matches) === 0) {
            $lower = strtolower($email);
            $lowerLit = xpath_literal($lower);
            // translate(email,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz') = 'lower'
            $matches = $xml->xpath("//user[translate(email, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = {$lowerLit}]");
        }
        if ($matches && count($matches) > 0) $user = $matches[0];
    } catch (Exception $e) {
        // ignore
    }
}

// Fallback: case-insensitive iteration over users
if (!$user && $xml && isset($xml->user)) {
    foreach ($xml->user as $u) {
        if (strtolower(trim((string)$u->email)) === strtolower($email)) { $user = $u; break; }
    }
}

if (!$user) {
    // Could not find the user; redirect with an error
    header('Location: /eventsphere/login.php?error=1');
    exit();
}
$hash = (string)$user->password;

if (!password_verify($password, $hash)) {
    header('Location: /eventsphere/login.php?error=1');
    exit();
}

// Successful login: set session data and redirect according to role
$_SESSION['user_id'] = (string)$user['id'];
$_SESSION['name'] = (string)$user->username;
// Also set username for older references
$_SESSION['username'] = (string)$user->username;
$_SESSION['role'] = (string)$user->role;

$role = strtolower(trim((string)$user->role));
if ($role === 'admin') {
    header('Location: /eventsphere/admin.php?tab=overview');
    exit();
}
// default: attendee
header('Location: /eventsphere/dashboard.php');
exit();
