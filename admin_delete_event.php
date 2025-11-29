<?php
/**
 * admin_delete_event.php
 * Deletes an event from data/events.xml and optionally removes related tickets.
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
if ($eventId === '') { header('Location: /eventsphere/admin.php?tab=events&msg=invalid'); exit(); }

$xh = new XmlHandler(__DIR__ . '/data/events.xml');
$xml = $xh->read(); if (!$xml) { header('Location: /eventsphere/admin.php?tab=events&msg=notfound'); exit(); }

$found = null; $index = 0; foreach ($xml->event as $i => $e) { if ((string)$e['id'] === $eventId) { $found = $e; break; } $index++; }
if (!$found) { header('Location: /eventsphere/admin.php?tab=events&msg=missing'); exit(); }

// Remove the event node
// SimpleXML does not have direct removeChild; use DOM for deletion
$dom = dom_import_simplexml($found);
if ($dom) {
    $domParent = $dom->parentNode;
    if ($domParent) $domParent->removeChild($dom);
}

// Optionally, remove related tickets for the event
$tx = new XmlHandler(__DIR__ . '/data/tickets.xml');
$tXml = $tx->read();
if ($tXml && $tXml->ticket) {
    $toDelete = [];
    foreach ($tXml->ticket as $t) {
        if ((string)$t->event_id === $eventId) {
            $toDelete[] = $t;
        }
    }
    foreach ($toDelete as $del) { $domT = dom_import_simplexml($del); if ($domT && $domT->parentNode) $domT->parentNode->removeChild($domT); }
    $tx->saveSimpleXML($tXml);
}

// Save events.xml
if ($xh->saveSimpleXML($xml)) { header('Location: /eventsphere/admin.php?tab=events&msg=deleted'); exit(); }
header('Location: /eventsphere/admin.php?tab=events&msg=delete_error'); exit();
?>
