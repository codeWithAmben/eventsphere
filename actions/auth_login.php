<?php
// actions/auth_login.php - wrapper that delegates to root auth_login.php
require_once __DIR__ . '/../auth_login.php';
// auth_login.php will handle POST and redirects
exit();
