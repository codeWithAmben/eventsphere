<?php
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/xml_handler.php';
require_once __DIR__ . '/../core/csrf.php';

$auth = new Auth();
$auth->requireLogin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') { header('HTTP/1.1 403 Forbidden'); exit(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /eventsphere/admin.php?tab=attendees'); exit(); }

$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_validate($csrf)) { header('Location: /eventsphere/admin.php?tab=attendees&msg=invalid'); exit(); }

$userId = trim($_POST['user_id'] ?? '');
$role = trim($_POST['role'] ?? 'attendee');

$xh = new XmlHandler(__DIR__ . '/../data/users.xml');
$xml = $xh->read();
if (!$xml) { header('Location: /eventsphere/admin.php?tab=attendees&msg=user_error'); exit(); }

$found = null; foreach ($xml->user as $u) { if ((string)$u['id'] === (string)$userId) { $found = $u; break; } }
if (!$found) { header('Location: /eventsphere/admin.php?tab=attendees&msg=user_notfound'); exit(); }

if (isset($found->role)) $found->role = $role; else $found->addChild('role', $role);

if ($xh->saveSimpleXML($xml)) {
    header('Location: /eventsphere/admin.php?tab=attendees&msg=user_saved'); exit();
}

header('Location: /eventsphere/admin.php?tab=attendees&msg=user_error'); exit();
?>
