<?php
// actions/auth_register.php
require_once __DIR__ . '/../core/xml_handler.php';
require_once __DIR__ . '/../core/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /eventsphere/register.php'); exit();
}

$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_validate($csrf)) {
    header('Location: /eventsphere/register.php?error=1'); exit();
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (!$username || !$email || !$password || $password !== $password_confirm) {
    header('Location: /eventsphere/register.php?error=1'); exit();
}

$xh = new XmlHandler(__DIR__ . '/../data/users.xml');
$xml = $xh->read();
if (!$xml) $xml = new SimpleXMLElement('<users></users>');

// Check for duplicate email
foreach ($xml->user as $u) {
    if (strtolower((string)$u->email) === strtolower($email)) {
        header('Location: /eventsphere/register.php?error=1'); exit();
    }
}

// Create new id based on highest existing id (numeric)
$maxId = 0;
foreach ($xml->user as $u) { $idVal = (int)$u['id']; if ($idVal > $maxId) $maxId = $idVal; }
$newId = $maxId + 1;

$user = $xml->addChild('user');
$user->addAttribute('id', $newId);
$user->addChild('username', htmlspecialchars($username));
$user->addChild('email', htmlspecialchars($email));
$user->addChild('password', password_hash($password, PASSWORD_DEFAULT));
$user->addChild('role', 'attendee');

if ($xh->saveSimpleXML($xml)) {
    header('Location: /eventsphere/register.php?msg=created'); exit();
}

header('Location: /eventsphere/register.php?error=1'); exit();
