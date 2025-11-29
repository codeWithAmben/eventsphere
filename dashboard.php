<?php
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/xml_handler.php';
$auth = new Auth();
$auth->requireLogin();

// 1. Hide the global top header because we are building a custom dashboard layout
$GLOBALS['hideGlobalHeader'] = true;
require_once __DIR__ . '/templates/header.php';

// Session + Security
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /eventsphere/login.php'); exit();
}

$userId = $_SESSION['user_id'];
$currentPage = basename($_SERVER['PHP_SELF']);

// --- DATA LOADING LOGIC (Same as before) ---

// Load User
$usersXml = new XmlHandler(__DIR__ . '/data/users.xml');
$users = $usersXml->read();
$currentUser = null;
if ($users && $users->user) {
    foreach ($users->user as $u) {
        if ((string)$u['id'] === (string)$userId) { $currentUser = $u; break; }
    }
}

// Set Session Name if missing
if (!isset($_SESSION['name']) && $currentUser) {
    $_SESSION['name'] = (string)($currentUser->name ?? $currentUser->username ?? 'Attendee');
}

// Load Tickets
$ticketsXml = new XmlHandler(__DIR__ . '/data/tickets.xml');
$tickets = $ticketsXml->read();
$myTicket = null;
$userTickets = [];
if ($tickets && $tickets->ticket) {
    foreach ($tickets->ticket as $t) {
        if ((string)$t->user_id === (string)$userId) {
            $userTickets[] = $t;
        }
    }
    $myTicket = count($userTickets) ? $userTickets[0] : null;
}

// Stats
$totalEventsAttended = count($userTickets);
$ticketsValidatedCount = 0;
if ($tickets && $tickets->ticket) {
    foreach ($tickets->ticket as $t) {
        if (isset($t->status) && (string)$t->status === 'validated') $ticketsValidatedCount++;
    }
}

// Chat Count
$chatLogsXml = new XmlHandler(__DIR__ . '/data/chat_logs.xml');
$chatLogs = $chatLogsXml->read();
$chatMessagesCount = 0;
if ($chatLogs && $chatLogs->message) {
    foreach ($chatLogs->message as $m) { $chatMessagesCount++; }
}

// Announcements
$annXmlHandler = new XmlHandler(__DIR__ . '/data/announcements.xml');
$annXml = $annXmlHandler->read();
$announcements = [];
if ($annXml && $annXml->announcement) {
    foreach ($annXml->announcement as $a) {
        $announcements[] = $a;
    }
    usort($announcements, function($a, $b){
        return strtotime((string)$b->timestamp ?? 0) <=> strtotime((string)$a->timestamp ?? 0);
    });
    $announcements = array_slice($announcements, 0, 3);
}

// Events & Upcoming
$eventsXml = new XmlHandler(__DIR__ . '/data/events.xml');
$events = $eventsXml->read();
$eventLookup = [];
$upcoming = [];
if ($events && $events->event) {
    foreach ($events->event as $evt) {
        $evtId = (string)$evt['id'] ?: (string)$evt->id;
        $eventLookup[$evtId] = $evt;
        
        $dateStr = (string)$evt->date;
        if ($dateStr) {
            $d = strtotime($dateStr);
            if ($d && $d > time()) {
                $upcoming[] = ['date' => $d, 'evt' => $evt];
            }
        }
    }
    usort($upcoming, function($a, $b){ return $a['date'] <=> $b['date']; });
    $upcoming = array_slice($upcoming, 0, 3);
}
?>

<div class="flex h-screen overflow-hidden bg-gray-50 font-['Open_Sans']">

    <?php require_once __DIR__ . '/templates/sidebar.php'; ?>

    <!-- sidebar backdrop and mobile sidebar are provided by templates/sidebar.php -->

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between shrink-0">
            
            <div class="flex items-center gap-4">
                <button id="sidebarToggle" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded" aria-controls="sidebar" aria-expanded="false">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                
                <nav class="text-sm font-medium text-slate-500">
                    <ol class="flex items-center gap-2">
                        <li><a href="index.php" class="text-slate-900 hover:text-indigo-600 transition">Home</a></li>
                        <li><i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i></li>
                        <li class="text-slate-900">Dashboard</li>
                    </ol>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-900 font-['Montserrat']">
                        <?php echo htmlspecialchars($_SESSION['name'] ?? 'Attendee'); ?>
                    </div>
                    <div class="text-xs text-slate-500 font-medium">
                        <?php echo htmlspecialchars($currentUser->role ?? 'Attendee'); ?>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden ring-2 ring-white shadow-sm">
                    <?php
                        // Avatar Logic
                        $avatarPath = '/eventsphere/assets/images/avatar-placeholder.svg';
                        if (isset($currentUser->avatar) && (string)$currentUser->avatar) {
                            $avatarPath = '/eventsphere/' . ltrim((string)$currentUser->avatar, '/');
                        }
                    ?>
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="Profile" class="w-full h-full object-cover">
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    
                    <div class="md:col-span-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h2 class="text-xl font-bold font-['Montserrat'] text-slate-800">My Digital Ticket</h2>
                                <p class="text-slate-500 text-sm mt-1">Keep this ready for event entry.</p>
                            </div>
                            <div class="flex gap-2">
                                <?php if ($myTicket): ?>
                                    <a href="view_ticket.php?id=<?php echo urlencode((string)$myTicket['id']); ?>" target="_blank" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition flex items-center gap-2">
                                        <i data-lucide="printer" class="w-4 h-4"></i> Print
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-6 items-stretch">
                            <div class="flex-1 bg-gradient-to-br from-slate-50 to-indigo-50/50 border border-indigo-100 rounded-lg p-5">
                                <?php if ($myTicket): 
                                    $ticketEventId = (string)($myTicket->event_id ?? $myTicket->event);
                                    $ticketEvent = $eventLookup[$ticketEventId] ?? null;
                                ?>
                                    <div class="flex justify-between items-start">
                                        <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded">CONFIRMED</span>
                                        <span class="text-xs text-slate-400 font-mono">ID: #<?php echo substr((string)$myTicket['id'], -6); ?></span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 mt-3 font-['Montserrat']">
                                        <?php echo htmlspecialchars($ticketEvent ? (string)$ticketEvent->title : 'Event Name'); ?>
                                    </h3>
                                    <div class="mt-4 space-y-2">
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <i data-lucide="calendar" class="w-4 h-4 text-indigo-400"></i>
                                            <?php echo htmlspecialchars($ticketEvent ? (string)$ticketEvent->date : 'Date TBD'); ?>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-indigo-400"></i>
                                            <?php echo htmlspecialchars($ticketEvent ? (string)$ticketEvent->venue : 'Venue TBD'); ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-6">
                                        <div class="inline-block p-3 bg-slate-100 rounded-full mb-3">
                                            <i data-lucide="ticket" class="w-6 h-6 text-slate-400"></i>
                                        </div>
                                        <h4 class="font-semibold text-slate-700">No Active Ticket</h4>
                                        <p class="text-xs text-slate-500 mt-1 mb-4">You are not registered for any upcoming events.</p>
                                        <a href="schedule.php" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Browse Schedule</a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="w-full md:w-48 bg-white border border-gray-100 rounded-lg p-4 flex items-center justify-center relative">
                                <?php if ($myTicket): ?>
                                    <?php $qrValue = (string)($myTicket->ticket_id ?? $myTicket['id']); ?>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($qrValue); ?>" alt="QR" class="max-w-full h-auto" />
                                <?php else: ?>
                                    <div class="text-center">
                                        <div class="w-24 h-24 bg-slate-50 rounded mx-auto flex items-center justify-center mb-2">
                                            <i data-lucide="qr-code" class="w-8 h-8 text-slate-300"></i>
                                        </div>
                                        <span class="text-xs text-slate-400">QR Code</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-4 bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                            <h3 class="text-lg font-bold font-['Montserrat'] text-slate-800">Live Updates</h3>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto pr-2 space-y-3">
                            <?php if (!empty($announcements)): ?>
                                <?php foreach ($announcements as $a): ?>
                                    <div class="p-3 bg-slate-50 rounded-lg border-l-4 border-indigo-500">
                                        <p class="text-sm text-slate-700 font-medium"><?php echo htmlspecialchars((string)$a->text); ?></p>
                                        <span class="text-xs text-slate-400 mt-1 block">Just now</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="h-full flex flex-col items-center justify-center text-slate-400">
                                    <i data-lucide="bell-off" class="w-8 h-8 mb-2 opacity-50"></i>
                                    <span class="text-sm">No new announcements</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    
                    <div class="md:col-span-4 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold font-['Montserrat'] text-slate-800">Upcoming</h3>
                            <a href="schedule.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View All</a>
                        </div>
                        
                        <ul class="space-y-3">
                            <?php if (!empty($upcoming)): ?>
                                <?php foreach ($upcoming as $u): $evt = $u['evt']; $ts = $u['date']; ?>
                                <li class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition">
                                    <div class="flex flex-col items-center justify-center w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg shrink-0">
                                        <span class="text-xs font-bold"><?php echo date('M', $ts); ?></span>
                                        <span class="text-lg font-bold leading-none"><?php echo date('d', $ts); ?></span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-semibold text-slate-900 truncate"><?php echo htmlspecialchars((string)$evt->title); ?></h4>
                                        <p class="text-xs text-slate-500 truncate"><?php echo date('h:i A', $ts); ?> • <?php echo htmlspecialchars((string)$evt->venue); ?></p>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-sm text-slate-500 text-center py-4">No upcoming sessions found.</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-500">Events Attended</span>
                            </div>
                            <div class="text-3xl font-bold text-slate-800 font-['Montserrat']"><?php echo (int)$totalEventsAttended; ?></div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                                    <i data-lucide="message-square" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-500">Messages Sent</span>
                            </div>
                            <div class="text-3xl font-bold text-slate-800 font-['Montserrat']"><?php echo (int)$chatMessagesCount; ?></div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                                    <i data-lucide="scan-line" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-500">Tickets Validated</span>
                            </div>
                            <div class="text-3xl font-bold text-slate-800 font-['Montserrat']"><?php echo (int)$ticketsValidatedCount; ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <a href="chat.php" class="fixed bottom-8 right-8 bg-indigo-600 hover:bg-indigo-700 text-white p-4 rounded-full shadow-lg transition-transform hover:scale-105 z-50 group">
        <i data-lucide="headphones" class="w-6 h-6"></i>
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Support</span>
    </a>

</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }
    }

    // Initialize Icons
    if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
        lucide.createIcons();
    }
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>