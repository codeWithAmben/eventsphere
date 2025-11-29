<?php
require_once __DIR__ . '/core/xml_handler.php';
require_once __DIR__ . '/core/auth.php';
// Enforce logins for schedule page
$auth = new Auth();
$auth->requireLogin();
// Hide global header and use sidebar-driven layout
$GLOBALS['hideGlobalHeader'] = true;
require_once __DIR__ . '/templates/header.php';

// authenticated user info
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$loggedIn = true;
$userId = $_SESSION['user_id'];

// Load Data
$events = (new XmlHandler(__DIR__ . '/data/events.xml'))->read();
$tickets = (new XmlHandler(__DIR__ . '/data/tickets.xml'))->read();

// Helper Functions
function isRegistered($ticketsXml, $eventId, $userId) {
    if (!$ticketsXml || !$ticketsXml->ticket) return false;
    foreach ($ticketsXml->ticket as $t) {
        if ((string)$t->event_id === (string)$eventId && (string)$t->user_id === (string)$userId) return true;
    }
    return false;
}

function getUserTicketId($ticketsXml, $eventId, $userId) {
  if (!$ticketsXml || !$ticketsXml->ticket) return null;
  foreach ($ticketsXml->ticket as $t) {
    if ((string)$t->event_id === (string)$eventId && (string)$t->user_id === (string)$userId) return (string)$t->ticket_id ?: (string)$t['id'];
  }
  return null;
}
?>

<div class="flex h-screen overflow-hidden bg-gray-50 font-['Open_Sans']">
    <?php require_once __DIR__ . '/templates/sidebar.php'; ?>
    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="flex items-center justify-between border-b bg-white px-4 md:px-8 py-4 mb-8">
                <div class="flex items-center gap-4">
                <button id="sidebarToggle" class="md:hidden p-2 rounded hover:bg-slate-100" aria-expanded="false" aria-controls="sidebar"><i data-lucide="menu" class="w-6 h-6 text-slate-600"></i></button>
                <nav class="text-sm text-slate-500" aria-label="Breadcrumb"><ol class="flex items-center gap-2"><li><a href="index.php" class="text-slate-900 hover:text-indigo-600">Home</a></li><li>&gt;</li><li class="text-slate-800">Schedule</li></ol></nav>
            </div>
            
        </div>
        <div class="bg-white border-b border-gray-200">
        <div class="w-full mx-auto px-4 py-12 max-w-none">
            <h1 class="text-3xl font-extrabold font-['Montserrat'] text-slate-900">Discover Events</h1>
            <p class="mt-2 text-slate-500 text-lg">Browse our upcoming tech summits, workshops, and networking sessions.</p>
        </div>
    </div>

    <div class="w-full mx-auto px-4 py-12 max-w-none">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            <?php if ($events && $events->event): ?>
                <?php foreach ($events->event as $e): 
                    // Date Parsing for the Visual Badge
                    $dateStr = (string)$e->date;
                    $timestamp = strtotime($dateStr);
                    $month = $timestamp ? date('M', $timestamp) : 'TBD';
                    $day = $timestamp ? date('d', $timestamp) : '--';
                    $time = $timestamp ? date('g:i A', $timestamp) : '';
                    
                    $registered = $loggedIn ? isRegistered($tickets, (string)$e['id'], $userId) : false;
                ?>
                
                <div class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden relative">
                    
                    <div class="h-32 bg-gradient-to-r from-indigo-600 to-teal-500 relative">
                        <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors"></div>
                        
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg p-2 text-center min-w-[60px] shadow-sm">
                            <div class="text-xs font-bold text-indigo-600 uppercase tracking-wide"><?php echo $month; ?></div>
                            <div class="text-2xl font-extrabold text-slate-900 leading-none"><?php echo $day; ?></div>
                        </div>
                        
                        <div class="absolute top-4 left-4">
                            <?php if ($registered): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500 text-white text-xs font-bold shadow-sm">
                                    <i data-lucide="check-circle-2" class="w-3 h-3"></i> Registered
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="text-xl font-bold font-['Montserrat'] text-slate-900 leading-tight group-hover:text-indigo-600 transition-colors">
                                <a href="/eventsphere/event-details.php?id=<?php echo urlencode((string)$e['id']); ?>">
                                    <?php echo htmlspecialchars((string)$e->title); ?>
                                </a>
                            </h3>
                        </div>

                        <div class="space-y-3 mb-6">
                            <div class="flex items-start gap-3 text-sm text-slate-600">
                                <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 mt-0.5 shrink-0"></i>
                                <span class="line-clamp-1"><?php echo htmlspecialchars((string)$e->venue); ?></span>
                            </div>
                            <?php if ($time): ?>
                            <div class="flex items-center gap-3 text-sm text-slate-600">
                                <i data-lucide="clock" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                <span><?php echo $time; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <p class="text-sm text-slate-500 line-clamp-2 mb-6 flex-1">
                            <?php echo htmlspecialchars((string)$e->description); ?>
                        </p>

                        <div class="pt-6 border-t border-slate-100 flex items-center justify-between gap-3">
                                     <a href="/eventsphere/event-details.php?id=<?php echo urlencode((string)$e['id']); ?>" 
                                         class="text-sm font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                                View Details
                            </a>

                            <?php if ($loggedIn): ?>
                                <?php if ($registered): ?>
                                    <?php $ticketId = getUserTicketId($tickets, (string)$e['id'], $userId); ?>
                                    <a href="/eventsphere/view_ticket.php?id=<?php echo urlencode($ticketId); ?>" 
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-bold hover:bg-emerald-100 transition-colors">
                                        <i data-lucide="ticket" class="w-4 h-4"></i> My Ticket
                                    </a>
                                <?php else: ?>
                                    <button type="button" 
                                            onclick="askConfirmation('Confirm Registration', 'Secure your spot for <?php echo htmlspecialchars(addslashes((string)$e->title)); ?>?', '/eventsphere/actions/register.php', '<?php echo htmlspecialchars((string)$e['id']); ?>', false)" 
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-all hover:-translate-y-0.5">
                                        Register
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="/eventsphere/login.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                                    Login to Join
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-16 text-center">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar-off" class="w-10 h-10 text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">No events found</h3>
                    <p class="text-slate-500">Check back later for upcoming sessions.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
    </div>
    </div>

<?php include __DIR__ . '/templates/global_confirm_modal.php'; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

<script src="https://unpkg.com/lucide@latest"></script>

<script>
    lucide.createIcons();
</script>