<?php
/**
 * actions/save_event.php
 * Save or update an event, including nested schedule and speakers arrays.
 */
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/xml_handler.php';
require_once __DIR__ . '/../core/csrf.php';

$auth = new Auth();
$auth->requireLogin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') { header('HTTP/1.1 403 Forbidden'); exit(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /eventsphere/admin.php?tab=events'); exit(); }

// CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_validate($csrf)) { header('Location: /eventsphere/admin.php?tab=events&msg=invalid'); exit(); }

$eventId = trim($_POST['id'] ?? '');
$title = trim($_POST['title'] ?? '');
$date = trim($_POST['date'] ?? '');
$venue = trim($_POST['venue'] ?? '');
$desc = trim($_POST['description'] ?? '');
$status = trim($_POST['status'] ?? '');
$capacity = trim($_POST['capacity'] ?? '');

// Arrays
$schedules = $_POST['schedule'] ?? [];
$speakers = $_POST['speakers'] ?? [];

$xh = new XmlHandler(__DIR__ . '/../data/events.xml');
$xml = $xh->read();
if (!$xml) { $xml = new SimpleXMLElement('<events></events>'); }

$found = null; foreach ($xml->event as $e) { if ((string)$e['id'] === $eventId) { $found = $e; break; } }

if (!$found) {
    // create new
    if ($eventId === '') $eventId = 'evt' . time();
    $found = $xml->addChild('event');
    $found->addAttribute('id', $eventId);
}

// Set basic fields
if ($title !== '') { if (isset($found->title)) $found->title = $title; else $found->addChild('title', $title); }
if ($date !== '') { if (isset($found->date)) $found->date = $date; else $found->addChild('date', $date); }
if ($venue !== '') { if (isset($found->venue)) $found->venue = $venue; else $found->addChild('venue', $venue); }
if ($desc !== '') { if (isset($found->description)) $found->description = $desc; else $found->addChild('description', $desc); }
if ($status !== '') { if (isset($found->status)) $found->status = $status; else $found->addChild('status', $status); }
if ($capacity !== '') { if (isset($found->capacity)) $found->capacity = $capacity; else $found->addChild('capacity', $capacity); }

// Remove old schedule and speakers nodes (if any)
if (isset($found->schedule)) {
    $dom = dom_import_simplexml($found->schedule);
    if ($dom && $dom->parentNode) $dom->parentNode->removeChild($dom);
}
if (isset($found->speakers)) {
    $dom = dom_import_simplexml($found->speakers);
    if ($dom && $dom->parentNode) $dom->parentNode->removeChild($dom);
}

// Add schedules
if (!empty($schedules) && is_array($schedules)) {
    $schNode = $found->addChild('schedule');
    foreach ($schedules as $sn) {
        $time = trim($sn['time'] ?? '');
        $titleS = trim($sn['title'] ?? '');
        $speaker = trim($sn['speaker'] ?? '');
        if ($time === '' && $titleS === '' && $speaker === '') continue;
        $sessionNode = $schNode->addChild('session');
        $sessionNode->addChild('time', $time);
        $sessionNode->addChild('title', $titleS);
        $sessionNode->addChild('speaker', $speaker);
    }
}

// Add speakers
if (!empty($speakers) && is_array($speakers)) {
    $spNode = $found->addChild('speakers');
    foreach ($speakers as $sp) {
        $name = trim($sp['name'] ?? '');
        $role = trim($sp['role'] ?? '');
        $avatar = trim($sp['avatar'] ?? '');
        if ($name === '' && $role === '') continue;
        $snode = $spNode->addChild('speaker');
        $snode->addChild('name', $name);
        $snode->addChild('role', $role);
        if ($avatar !== '') $snode->addChild('avatar', $avatar);
    }
}

if ($xh->saveSimpleXML($xml)) {
    header('Location: /eventsphere/admin.php?tab=events&msg=event_saved'); exit();
}

header('Location: /eventsphere/admin.php?tab=events&msg=event_error'); exit();
?>
