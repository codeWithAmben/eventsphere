<?php
// Simulate a logged-in user and render the dashboard (CLI test)
session_start();
$_SESSION['user_id'] = '2';
$_SESSION['name'] = 'test';
$_SESSION['username'] = 'test';
$_SESSION['role'] = 'attendee';

require __DIR__ . '/../dashboard.php';

?>