<?php
require_once __DIR__ . '/core/xml_handler.php';
require_once __DIR__ . '/core/auth.php';

// Only allow logged-in users to view tickets (ownership check)
$auth = new Auth();
$auth->requireLogin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$userId = $_SESSION['user_id'] ?? null;

$ticketId = trim($_GET['id'] ?? '');
if ($ticketId === '') {
    header('HTTP/1.1 400 Bad Request');
    echo 'Missing ticket id';
    exit();
}

$xhTickets = new XmlHandler(__DIR__ . '/data/tickets.xml');
$ticketsXml = $xhTickets->read();
$found = null;
if ($ticketsXml && $ticketsXml->ticket) {
    foreach ($ticketsXml->ticket as $t) {
        if ((string)$t['ticket_id'] === $ticketId || (string)$t['id'] === $ticketId) { $found = $t; break; }
    }
}
if (!$found) {
    header('HTTP/1.1 404 Not Found');
    echo 'Ticket not found';
    exit();
}

// Only owner can view
if ($userId === null || (string)$found->user_id !== (string)$userId) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied';
    exit();
}

$eventId = (string)$found->event_id;
$xhEvents = new XmlHandler(__DIR__ . '/data/events.xml');
$eventsXml = $xhEvents->read();
$event = null;
if ($eventsXml && $eventsXml->event) {
    foreach ($eventsXml->event as $e) {
        if ((string)$e['id'] === $eventId) { $event = $e; break; }
    }
}

$ticket = [
    'ticket_id' => (string)$found->ticket_id,
    'user_id' => (string)$found->user_id,
    'event_id' => $eventId,
    'registration_date' => (string)$found->registration_date,
    'qr_code_string' => (string)$found->qr_code_string,
];

// Render the print-friendly ticket page
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EventSphere+ Ticket - <?php echo htmlspecialchars($ticket['ticket_id']); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media print {
      .no-print { display: none !important; }
      .ticket { box-shadow: none !important; }
      body { background: none !important; }
    }
  </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen py-12">
  <div class="ticket bg-white shadow-lg rounded-lg p-6 w-full max-w-2xl">
    <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8">
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-indigo-600">EventSphere+</h1>
        <div class="mt-2 text-slate-600">Boarding Pass</div>
        <hr class="my-4">
        <div>
          <div class="text-slate-500 text-sm">Ticket ID</div>
          <div class="text-lg font-semibold"><?php echo htmlspecialchars($ticket['ticket_id']); ?></div>
        </div>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <div class="text-slate-500 text-sm">Event</div>
            <div class="text-md font-semibold"><?php echo htmlspecialchars($event ? (string)$event->title : 'Unknown Event'); ?></div>
            <div class="text-slate-500 text-sm mt-1">Venue</div>
            <div class="text-md"><?php echo htmlspecialchars($event ? (string)$event->venue : 'Unknown Venue'); ?></div>
          </div>
          <div>
            <div class="text-slate-500 text-sm">Registered</div>
            <div class="text-md"><?php echo htmlspecialchars($ticket['registration_date']); ?></div>
            <div class="mt-3 text-slate-500 text-sm">Holder</div>
            <div class="text-md"><?php echo htmlspecialchars((string)($_SESSION['name'] ?? $_SESSION['username'] ?? '')); ?></div>
          </div>
        </div>
      </div>

      <div class="flex-none text-center">
        <img class="mx-auto border p-2 bg-white rounded" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($ticket['ticket_id']); ?>" alt="QR code" />
        <div class="mt-2 text-xs text-slate-500">Scan at the entrance</div>
      </div>
    </div>
    <div class="mt-6 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <a class="no-print inline-flex items-center px-4 py-2 rounded border" href="/eventsphere/dashboard.php">Back</a>
        <?php if (!empty($eventId) && $eventId !== ''): ?>
            <a class="no-print inline-flex items-center px-4 py-2 rounded border bg-white text-slate-800" href="/eventsphere/event-details.php?id=<?php echo urlencode($eventId); ?>">View Event</a>
        <?php endif; ?>
      </div>
      <button class="no-print inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded" onclick="window.print()">Print Ticket</button>
    </div>
  </div>
</body>
</html>
<?php
?>
