<?php
require_once __DIR__ . '/../core/xml_handler.php';
if ($argc < 2) { echo "Usage: php check_passwords.php <password>\n"; exit(1);} 
$pw = $argv[1];
$xh = new XmlHandler(__DIR__ . '/../data/users.xml');
$xml = $xh->read();
if (!$xml) { echo "No users.xml loaded\n"; exit(1);} 
foreach ($xml->user as $u) {
    $email = (string)$u->email;
    $hash = (string)$u->password;
    $ok = password_verify($pw, $hash);
    echo sprintf("%s => %s\n", $email, $ok ? 'MATCH' : 'NO MATCH');
}
?>