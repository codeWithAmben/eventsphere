<?php
/**
 * get_event_json.php
 * Return event JSON for the admin edit modal (Admin-only)
 */
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/xml_handler.php';
$auth = new Auth();
$auth->requireLogin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('HTTP/1.1 403 Forbidden'); echo json_encode(['error' => 'forbidden']); exit();
}

$id = trim($_GET['id'] ?? '');
if ($id === '') { header('HTTP/1.1 400 Bad Request'); echo json_encode(['error' => 'missing id']); exit(); }

$xh = new XmlHandler(__DIR__ . '/data/events.xml');
$eventsXml = $xh->read();
if (!$eventsXml) { header('HTTP/1.1 404 Not Found'); echo json_encode(['error' => 'not found']); exit(); }

$out = null;
foreach ($eventsXml->event as $e) {
    if ((string)$e['id'] === (string)$id) {
        $out = [
            'id' => (string)$e['id'],
            'title' => (string)$e->title,
            'date' => (string)$e->date,
            'venue' => (string)$e->venue,
            'description' => (string)$e->description,
            'status' => (string)$e->status,
            'capacity' => (string)$e->capacity ?? null,
            'sessions' => [],
            'speakers' => [],
        ];
        if (isset($e->schedule) && $e->schedule->session) {
            foreach ($e->schedule->session as $s) {
                $out['sessions'][] = ['time' => (string)$s->time, 'title' => (string)$s->title, 'speaker' => (string)$s->speaker];
            }
        }
        if (isset($e->speakers) && $e->speakers->speaker) {
            foreach ($e->speakers->speaker as $sp) {
                $out['speakers'][] = ['name' => (string)$sp->name, 'role' => (string)$sp->role, 'avatar' => (string)$sp->avatar];
            }
        }
        break;
    }
}

if (!$out) { header('HTTP/1.1 404 Not Found'); echo json_encode(['error' => 'not found']); exit(); }

header('Content-Type: application/json');
echo json_encode($out);
exit();
?>
