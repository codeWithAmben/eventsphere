<?php
/**
 * admin_post_announcement.php
 * Adds a new announcement to data/announcements.xml and keeps only the latest 5
 */
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/xml_handler.php';
require_once __DIR__ . '/core/csrf.php';

$auth = new Auth();
$auth->requireLogin();
// Only admin allowed
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /eventsphere/admin.php?tab=overview');
    exit();
}

// CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_validate($csrf)) { header('Location: /eventsphere/admin.php?tab=overview&msg=invalid'); exit(); }

$text = trim($_POST['text'] ?? '');
if ($text === '') {
    header('Location: /eventsphere/admin.php?tab=overview&msg=empty');
    exit();
}

$xh = new XmlHandler(__DIR__ . '/data/announcements.xml');
$xml = $xh->read();
if (!$xml) {
    $xml = new SimpleXMLElement('<announcements></announcements>');
}

// Add new announcement
$ann = $xml->addChild('announcement');
$ann->addChild('text', htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
$ann->addChild('timestamp', date('c'));

// Keep only latest 5 announcements by timestamp descending
$items = [];
foreach ($xml->announcement as $a) {
    $items[] = $a;
}
usort($items, function($a, $b) {
    $ta = strtotime((string)$a->timestamp ?? 0);
    $tb = strtotime((string)$b->timestamp ?? 0);
    return $tb <=> $ta;
});

// Rebuild XML with only top 5 (create a new container)
$newXml = new SimpleXMLElement('<announcements></announcements>');
foreach (array_slice($items, 0, 5) as $a) {
    $n = $newXml->addChild('announcement');
    $n->addChild('text', (string)$a->text);
    $n->addChild('timestamp', (string)$a->timestamp);
}

if ($xh->saveSimpleXML($newXml)) {
    header('Location: /eventsphere/admin.php?tab=overview&msg=success');
    exit();
}

header('Location: /eventsphere/admin.php?tab=overview&msg=error');
exit();
?>