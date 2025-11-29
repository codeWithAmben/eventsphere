<?php
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/xml_handler.php';
$GLOBALS['hideGlobalHeader'] = true;
require_once __DIR__ . '/templates/header.php';
$auth = new Auth();
$auth->requireLogin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$userId = $_SESSION['user_id'];

$ticketsXml = (new XmlHandler(__DIR__ . '/data/tickets.xml'))->read();
$eventsXml = (new XmlHandler(__DIR__ . '/data/events.xml'))->read();
$eventLookup = [];
if ($eventsXml && $eventsXml->event) {
    foreach ($eventsXml->event as $e) $eventLookup[(string)$e['id']] = $e;
}
$userTickets = [];
if ($ticketsXml && $ticketsXml->ticket) {
    foreach ($ticketsXml->ticket as $t) {
        if ((string)$t->user_id === (string)$userId) $userTickets[] = $t;
    }
}
?>
<div class="flex h-screen overflow-hidden bg-gray-50 font-['Open_Sans']">
  <?php require_once __DIR__ . '/templates/sidebar.php'; ?>
  <div class="flex-1 overflow-y-auto p-6 md:p-8">
    <div class="flex items-center justify-between border-b bg-white px-4 md:px-8 py-4 mb-8">
        <div class="flex items-center gap-4">
            <button id="sidebarToggle" class="md:hidden p-2 rounded hover:bg-slate-100" aria-expanded="false" aria-controls="sidebar"><i data-lucide="menu" class="w-6 h-6 text-slate-600"></i></button>
            <nav class="text-sm text-slate-500" aria-label="Breadcrumb"><ol class="flex items-center gap-2"><li><a href="/eventsphere/index.php" class="text-slate-900 hover:text-indigo-600">Home</a></li><li>&gt;</li><li class="text-slate-800">My Tickets</li></ol></nav>
        </div>
    </div>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold">My Tickets</h1>
    <div class="mt-4">
      <?php if (count($userTickets) === 0): ?>
        <div class="text-slate-500">You have no tickets yet. Visit the <a href="/eventsphere/index.php#schedule" class="text-indigo-600">schedule</a> to register.</div>
      <?php else: ?>
        <table class="w-full text-left table-auto">
          <thead>
            <tr class="text-slate-500 text-sm">
              <th class="px-3 py-2">Ticket ID</th>
              <th class="px-3 py-2">Event</th>
              <th class="px-3 py-2">Date</th>
              <th class="px-3 py-2">Registered On</th>
              <th class="px-3 py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($userTickets as $ut): ?>
              <tr class="border-t">
                <td class="px-3 py-2"><?php echo htmlspecialchars((string)$ut->ticket_id ?: (string)$ut['id']); ?></td>
                <td class="px-3 py-2"><?php $ev = $eventLookup[(string)$ut->event_id] ?? null; echo htmlspecialchars($ev ? (string)$ev->title : (string)$ut->event_id); ?></td>
                <td class="px-3 py-2"><?php echo htmlspecialchars($ev ? (string)$ev->date : ''); ?></td>
                <td class="px-3 py-2"><?php echo htmlspecialchars((string)$ut->registration_date); ?></td>
                <td class="px-3 py-2">
                  <a class="px-3 py-2 rounded border text-sm" href="/eventsphere/view_ticket.php?id=<?php echo urlencode((string)$ut->ticket_id ?: (string)$ut['id']); ?>">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
