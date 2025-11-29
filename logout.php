<?php
require_once __DIR__ . '/core/auth.php';
$auth = new Auth();
$auth->logout();
header('Location: /eventsphere/index.php');
exit();
?>