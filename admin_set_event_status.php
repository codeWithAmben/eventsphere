<?php
/**
 * admin_set_event_status.php
 * Quick endpoint to change status for an event (admin only).
 */
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/xml_handler.php';
require_once __DIR__ . '/core/csrf.php';
$auth = new Auth();
$auth->requireLogin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /eventsphere/admin.php?tab=events'); exit(); }

// CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_validate($csrf)) { header('Location: /eventsphere/admin.php?tab=events&msg=invalid'); exit(); }
$eventId = trim($_POST['event_id'] ?? '');
$status = trim($_POST['status'] ?? '');
if ($eventId === '' || $status === '') { header('Location: /eventsphere/admin.php?tab=events&msg=invalid'); exit(); }

$xh = new XmlHandler(__DIR__ . '/data/events.xml');
$xml = $xh->read(); if (!$xml) { header('Location: /eventsphere/admin.php?tab=events&msg=notfound'); exit(); }

$found = null; foreach ($xml->event as $e) { if ((string)$e['id'] === $eventId) { $found = $e; break; } }
if (!$found) { header('Location: /eventsphere/admin.php?tab=events&msg=missing'); exit(); }

if (isset($found->status)) $found->status = $status; else $found->addChild('status', $status);

if ($xh->saveSimpleXML($xml)) { header('Location: /eventsphere/admin.php?tab=events&msg=status_saved'); exit(); }
header('Location: /eventsphere/admin.php?tab=events&msg=status_error');
exit();
?>