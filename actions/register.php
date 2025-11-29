<?php
/**
 * actions/register.php
 * Register the currently logged-in user for an event. Creates a ticket in data/tickets.xml.
 * Expected input: POST{ event_id }
 * Outputs:
 *  - Redirects to /eventsphere/dashboard.php?success=registered on success
 *  - Redirects to /eventsphere/dashboard.php?error=... on failure
 */
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/xml_handler.php';
require_once __DIR__ . '/../core/csrf.php';

$auth = new Auth();
$auth->requireLogin(); // will redirect to login.php if not logged in

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// Use the user id from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: /eventsphere/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /eventsphere/dashboard.php');
    exit();
}

// CSRF validation
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_validate($csrf)) { header('Location: /eventsphere/dashboard.php?error=csrf'); exit(); }

$eventId = trim($_POST['event_id'] ?? '');
if ($eventId === '') {
    header('Location: /eventsphere/dashboard.php?error=invalid_event');
    exit();
}

// Validate event exists
$xhEvents = new XmlHandler(__DIR__ . '/../data/events.xml');
$eventsXml = $xhEvents->read();
$foundEvent = null;
if ($eventsXml && $eventsXml->event) {
    foreach ($eventsXml->event as $e) {
        if ((string)$e['id'] === $eventId) { $foundEvent = $e; break; }
    }
}
if (!$foundEvent) {
    header('Location: /eventsphere/dashboard.php?error=event_not_found');
    exit();
}

// Load tickets and check duplicate
$xhTickets = new XmlHandler(__DIR__ . '/../data/tickets.xml');
$ticketsXml = $xhTickets->read();
if (!$ticketsXml) {
    $ticketsXml = new SimpleXMLElement('<tickets></tickets>');
}
// Duplicate check
foreach ($ticketsXml->ticket as $t) {
    if ((string)$t->event_id === $eventId && (string)$t->user_id === (string)$userId) {
        header('Location: /eventsphere/dashboard.php?error=already_registered');
        exit();
    }
}

// Generate unique ticket id
// Keep trying if it collides (extremely rare)
do {
    $ticketId = strtoupper(uniqid('TKT-'));
    $exists = false;
    foreach ($ticketsXml->ticket as $t) {
        if ((string)$t['id'] === $ticketId) { $exists = true; break; }
    }
} while ($exists);

// Generate QR code string
try {
    $qrString = bin2hex(random_bytes(10));
} catch (Exception $ex) {
    $qrString = bin2hex(openssl_random_pseudo_bytes(10));
}

$newTicket = $ticketsXml->addChild('ticket');
$newTicket->addAttribute('id', $ticketId);
$newTicket->addChild('ticket_id', $ticketId);
$newTicket->addChild('user_id', (string)$userId);
$newTicket->addChild('event_id', (string)$eventId);
$newTicket->addChild('registration_date', gmdate('c'));
$newTicket->addChild('qr_code_string', (string)$qrString);

if ($xhTickets->saveSimpleXML($ticketsXml)) {
    header('Location: /eventsphere/dashboard.php?success=registered');
    exit();
}

header('Location: /eventsphere/dashboard.php?error=save_failed');
exit();
?>
