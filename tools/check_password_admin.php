<?php
// Quick tool: verify admin password against stored hash
$xml = simplexml_load_file(__DIR__ . '/../data/users.xml');
foreach ($xml->user as $u) {
    if ((string)$u->email === 'admin@eventsphere.com') {
        echo (string)$u->email . "\n";
        echo (string)$u->password . "\n";
        $ok = password_verify('AdminPass123!', (string)$u->password);
        echo ($ok ? 'PASSWORD OK\n' : 'PASSWORD FAIL\n');
    }
}
?>