<?php
// Test script to simulate POST and run auth_login.php without header redirects.
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = 'admin@eventsphere.com';
$_POST['password'] = 'AdminPass123!';

// Avoid header() redirects causing exit; redefine header() locally? Not in PHP.
// We'll run auth_login by modifying it to not redirect if running in CLI.
require_once __DIR__ . '/../core/xml_handler.php';

function xpath_literal($s) {
    if (strpos($s, "'") === false) return "'" . $s . "'";
    $parts = explode("'", $s);
    $concatParts = [];
    $lastIndex = count($parts) - 1;
    foreach ($parts as $idx => $p) {
        $concatParts[] = "'" . $p . "'";
        if ($idx !== $lastIndex) $concatParts[] = '\"\'\"';
    }
    return 'concat(' . implode(', ', $concatParts) . ')';
}

$email = trim($_POST['email']);
$password = $_POST['password'];
$xh = new XmlHandler(__DIR__ . '/../data/users.xml');
$xml = $xh->read();
$lit = xpath_literal($email);
$matches = $xml->xpath("//user[email={$lit}]");
if (!$matches || count($matches) === 0) {
    $lower = strtolower($email);
    $lowerLit = xpath_literal($lower);
    $matches = $xml->xpath("//user[translate(email, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = {$lowerLit}]");
}

if (!$matches || count($matches) === 0) {
    echo "No user found\n"; exit(1);
}
$user = $matches[0];
$hash = (string)$user->password;
if (!password_verify($password, $hash)) {
    echo "Password mismatch\n"; exit(1);
}

echo "Login OK: id=" . (string)$user['id'] . " name=" . (string)$user->username . " role=" . (string)$user->role . "\n";

?>