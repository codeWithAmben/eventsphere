<?php
$xml = simplexml_load_file(__DIR__ . '/../data/users.xml');
foreach ($xml->user as $u) {
    if ((string)$u->email === 'admin@example.com') {
        echo (password_verify('admin123', (string)$u->password) ? 'OK' : 'FAIL') . PHP_EOL;
    }
}
?>