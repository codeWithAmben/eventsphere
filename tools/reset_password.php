<?php
require_once __DIR__ . '/../core/xml_handler.php';

if (php_sapi_name() !== 'cli') {
    echo "This script is for CLI only.\n";
    exit(1);
}
if ($argc < 3) {
    echo "Usage: php reset_password.php <email> <new_password>\n";
    exit(1);
}
$email = $argv[1];
$newPassword = $argv[2];

$xh = new XmlHandler(__DIR__ . '/../data/users.xml');
$xml = $xh->read();
if (!$xml) {
    echo "Could not load users.xml\n";
    exit(1);
}

$found = null;
foreach ($xml->user as $u) {
    if (strtolower(trim((string)$u->email)) === strtolower(trim($email))) {
        $found = $u; break;
    }
}
if (!$found) {
    echo "User with email {$email} not found.\n";
    exit(1);
}

$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$found->password = $hash;

if ($xh->saveSimpleXML($xml)) {
    echo "Password updated for {$email}.\n";
} else {
    echo "Failed to update password.\n";
}

?>