<?php
/**
 * event-details.php
 * Full event page with hero, timeline, speakers, and register flow.
 * This file includes mock data arrays at the top so the page renders even without XML files present.
 */
require_once __DIR__ . '/core/xml_handler.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/templates/header.php';

// --- Mock data (design-time) - If XML exists, we'll overwrite these values below. Keep minimal fallback.
$event = [
  'id' => '',
  'title' => 'Sample Event',
  'date' => date('Y-m-d\TH:i:00'),
  'venue' => 'Main Auditorium',
  'description' => "No event selected.",
  'address' => '',
  'image' => '',
];

$sessions = [];
$speakers = [];

// Track registration status by the current user
$isRegistered = false;
$currentUserId = null;
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (isset($_SESSION['user_id'])) $currentUserId = $_SESSION['user_id'];

// Try to load real event from XML if id present in query string
// Pick event ID from query string; if none provided, fall back to the first event available.
$id = trim($_GET['id'] ?? '');
// Read events file and use provided id or default to the first event in the file.
$xhEvents = new XmlHandler(__DIR__ . '/data/events.xml');
$eventsXml = $xhEvents->read();
if (!$id && $eventsXml && $eventsXml->event) {
  // default to the first event's id
  $firstEvt = $eventsXml->event[0];
  $id = (string)$firstEvt['id'];
}

if ($id !== '') {
  // events already loaded into $eventsXml above when we needed a fallback id
    if ($eventsXml && $eventsXml->event) {
        foreach ($eventsXml->event as $e) {
            if ((string)$e['id'] === (string)$id) {
                $event = [
                    'id' => (string)$e['id'],
                    'title' => (string)$e->title,
                    'date' => (string)$e->date,
                    'venue' => (string)$e->venue,
                  'description' => (string)$e->description ?? '',
                    'address' => (string)$e->address ?? '',
                    'image' => (string)$e->image ?? '',
                  'status' => (string)$e->status ?? 'Scheduled',
                ];
                // Optionally extract sessions & speakers if present in XML
                // XML uses <schedule><session> .. </session></schedule>
                if (isset($e->schedule) && $e->schedule->session) {
                  $sessions = [];
                  foreach ($e->schedule->session as $s) {
                    $sessions[] = ['time' => (string)$s->time, 'title' => (string)$s->title, 'speaker' => (string)$s->speaker];
                  }
                }
                // --- Speakers: fetch from XML stored by admin create/edit form ---
                // The admin modal saves speakers as <speakers><speaker><name/><role/><avatar/></speaker></speakers>
                if (isset($e->speakers) && $e->speakers->speaker) {
                  $speakers = [];
                  foreach ($e->speakers->speaker as $sp) {
                    // Use 'role' primarily; legacy fallback to 'title' for older data
                    $roleValue = trim((string)$sp->role) ?: trim((string)$sp->title);
                    $avatarValue = trim((string)$sp->avatar);
                    // Normalise outputs and store string values
                    $speakers[] = [
                      'name' => (string)$sp->name,
                      'role' => $roleValue,
                      'avatar' => $avatarValue
                    ];
                  }
                }
                break;
            }
        }
    }
}
    $ticketsSoldCount = 0;
    // Count tickets sold for this event
    $allTickets = (new XmlHandler(__DIR__ . '/data/tickets.xml'))->read();
    if ($allTickets && $allTickets->ticket) {
      foreach ($allTickets->ticket as $tk) {
        if ((string)$tk->event_id === (string)$event['id']) $ticketsSoldCount++;
      }
    }

    $eventCapacity = null;
    if (isset($e) && isset($e->capacity)) $eventCapacity = (int)$e->capacity;
    $capacityReached = ($eventCapacity !== null && $ticketsSoldCount >= $eventCapacity);

  // Check tickets.xml to determine if current user already registered for this event
if ($currentUserId) {
    $xhTickets = new XmlHandler(__DIR__ . '/data/tickets.xml');
    $ticketsXml = $xhTickets->read();
    if ($ticketsXml && $ticketsXml->ticket) {
        foreach ($ticketsXml->ticket as $t) {
            if ((string)$t->event_id === (string)$event['id'] && (string)$t->user_id === (string)$currentUserId) {
                $isRegistered = true;
                $myTicketId = (string)$t->ticket_id ?: (string)$t['id'];
                break;
            }
        }
    }
}

// Render page
?>
<div class="min-h-screen bg-slate-50">
  <!-- Hero -->
  <?php $heroBgStyle = ($event['image']) ? 'style="background-image: linear-gradient(to right, rgba(15,23,42,0.85), rgba(67,56,202,0.65)), url(' . htmlspecialchars($event['image']) . '); background-size: cover; background-position:center;"' : ''; ?>
  <section <?php echo $heroBgStyle; ?> class="bg-gradient-to-r from-slate-900 to-indigo-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="md:flex md:items-center md:justify-between">
        <div>
          <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight"><?php echo htmlspecialchars($event['title']); ?></h1>
          <p class="mt-3 text-slate-300 text-lg flex items-center gap-4">
              <span class="inline-flex items-center gap-2">
              <i data-lucide="calendar" class="w-4 h-4"></i>
              <?php echo date('M j, Y g:i A', strtotime($event['date'])); ?>
            </span>
            <span class="inline-flex items-center gap-2">
              <i data-lucide="map-pin" class="w-4 h-4"></i>
              <?php echo htmlspecialchars($event['venue']); ?>
            </span>
            <?php if ($eventCapacity !== null): ?>
              <span class="inline-flex items-center gap-2 text-sm text-amber-200 ml-4">
                <i data-lucide="users" class="w-4 h-4"></i>
                <?php echo htmlspecialchars($ticketsSoldCount); ?> / <?php echo htmlspecialchars($eventCapacity); ?> seats
              </span>
            <?php endif; ?>
            <?php if (!empty($event['status'])): ?>
              <?php $statusClass = (strtolower($event['status']) === 'live now') ? 'bg-emerald-600' : 'bg-slate-700'; ?>
              <span class="inline-flex items-center gap-2 text-sm ml-2 px-2 py-1 rounded <?php echo $statusClass; ?>">
                <?php echo htmlspecialchars($event['status']); ?>
              </span>
            <?php endif; ?>
          </p>
        </div>
        <div class="mt-6 md:mt-0">
          <?php if ($currentUserId): ?>
            <?php if ($isRegistered): ?>
              <div class="flex items-center gap-3">
                <button class="px-6 py-3 rounded-md bg-gray-300 text-slate-700 font-semibold" disabled>Already Registered ✓</button>
                <a href="/eventsphere/view_ticket.php?id=<?php echo htmlspecialchars(urlencode($myTicketId)); ?>" class="px-6 py-3 rounded-md border bg-white text-slate-800">View Ticket</a>
              </div>
            <?php else: ?>
              <?php if (isset($capacityReached) && $capacityReached): ?>
                <div class="flex items-center gap-3">
                  <button class="px-6 py-3 rounded-md bg-gray-300 text-slate-700 font-semibold" disabled>Sold Out</button>
                </div>
              <?php else: ?>
              <form method="post" action="/eventsphere/actions/register.php">
                <?php echo csrf_input_field(); ?>
                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                <button type="submit" class="px-6 py-3 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">Register Now</button>
              </form>
              <?php endif; ?>
            <?php endif; ?>
          <?php else: ?>
            <a href="/eventsphere/login.php" class="px-6 py-3 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">Login to Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Main content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="md:grid md:grid-cols-3 md:gap-8">
      <!-- Left: Main content (2/3) -->
      <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-semibold">About the Event</h2>
          <p class="mt-3 text-slate-700"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold">Event Schedule</h3>
          <div class="mt-4 space-y-4">
            <?php foreach ($sessions as $s): ?>
              <div class="flex items-start gap-4">
                <div class="flex-shrink-0 mt-1">
                  <i data-lucide="circle" class="w-3 h-3 text-indigo-600"></i>
                </div>
                <div>
                  <div class="text-sm text-slate-500"><?php echo htmlspecialchars($s['time']); ?></div>
                  <div class="font-semibold"><?php echo htmlspecialchars($s['title']); ?></div>
                  <div class="text-sm text-slate-500"><?php echo htmlspecialchars($s['speaker']); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold">Featured Speakers</h3>
          <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ($speakers as $sp): ?>
              <div class="flex items-center gap-4 p-3 border rounded">
                <?php 
                  // Normalize avatar path: use provided path; if it's relative, prefix with /eventsphere/; otherwise use placeholder
                  $rawAvatar = (!empty($sp['avatar'])) ? trim((string)$sp['avatar']) : '';
                  if ($rawAvatar !== '') {
                    if (!preg_match('#^https?://#i', $rawAvatar) && strpos($rawAvatar, '/') !== 0) {
                      $rawAvatar = '/eventsphere/' . ltrim($rawAvatar, '/');
                    }
                    $spAvatar = htmlspecialchars($rawAvatar);
                  } else {
                    $spAvatar = '/eventsphere/assets/images/avatar-placeholder.svg';
                  }
                ?>
                <img src="<?php echo $spAvatar; ?>" alt="<?php echo htmlspecialchars($sp['name']); ?>" class="w-12 h-12 rounded-full object-cover">
                <div>
                  <div class="font-semibold"><?php echo htmlspecialchars($sp['name']); ?></div>
                  <div class="text-sm text-slate-500"><?php echo htmlspecialchars($sp['role'] ?? ''); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Right: Sidebar (1/3) -->
      <aside class="md:col-span-1 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold">Event Location</h4>
          <div class="mt-3 text-sm text-slate-600"><?php echo htmlspecialchars($event['venue']); ?></div>
          <div class="mt-1 text-sm text-slate-500"><?php echo htmlspecialchars($event['address']); ?></div>
          <div class="mt-4 bg-slate-100 rounded p-2 text-center text-sm text-slate-500">Interactive Venue Map (placeholder)</div>
          <!-- TODO: Integrate JS image map or a Leaflet/Mapbox map here for seat/room selection -->
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold">Share</h4>
          <div class="mt-3 flex items-center gap-3">
            <a href="#" class="inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50"><i data-lucide="share-2" class="w-5 h-5"></i></a>
            <a href="#" class="inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50"><i data-lucide="share-2" class="w-5 h-5"></i></a>
            <a href="#" class="inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50"><i data-lucide="share-2" class="w-5 h-5"></i></a>
          </div>
        </div>

      </aside>
    </div>
  </main>
</div>

<?php
require_once __DIR__ . '/templates/footer.php';
?>
