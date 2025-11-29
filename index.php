<?php 
require_once __DIR__ . '/core/auth.php'; // Ensure session is started if needed
require_once __DIR__ . '/templates/header.php'; 
require_once __DIR__ . '/core/xml_handler.php';

// --- DATA LOADING LOGIC ---

// Load events
$eventsXml = (new XmlHandler(__DIR__ . '/data/events.xml'))->read();
$upcomingEvent = null;

if ($eventsXml && $eventsXml->event) {
    $now = time();
    $nextTs = null;
    foreach ($eventsXml->event as $evt) {
        $dateStr = (string)$evt->date;
        if (!$dateStr) continue;
        $ts = strtotime($dateStr);
        if ($ts === false) continue;
        
        // Find the soonest future event
        if ($ts > $now && ($nextTs === null || $ts < $nextTs)) { 
            $nextTs = $ts; 
            $upcomingEvent = $evt; 
        }
    }
}

// Load tickets count
$ticketsXml = (new XmlHandler(__DIR__ . '/data/tickets.xml'))->read();
$ticketsSoldCount = 0;
if ($ticketsXml && $ticketsXml->ticket) {
    foreach ($ticketsXml->ticket as $t) { $ticketsSoldCount++; }
}

// Build Agenda (Aggregated Sessions)
$allSessions = [];
if ($eventsXml && $eventsXml->event) {
    foreach ($eventsXml->event as $evt) {
        $evtDateStr = (string)$evt->date;
        $evtTs = strtotime($evtDateStr ?: '');
        
        if (isset($evt->schedule) && $evt->schedule->session) {
            foreach ($evt->schedule->session as $session) {
                $stime = trim((string)$session->time);
                $sessTs = $evtTs;
                
                // Try to create a real timestamp
                if ($stime && $evtTs) {
                    $constructed = date('Y-m-d', $evtTs) . ' ' . $stime;
                    $tmp = strtotime($constructed);
                    if ($tmp !== false) $sessTs = $tmp;
                }
                
                $allSessions[] = [
                    'ts' => $sessTs ?: ($evtTs ?: time()),
                    'time' => $stime,
                    'title' => (string)$session->title,
                    'speaker' => (string)$session->speaker,
                    'eventTitle' => (string)$evt->title,
                    'eventId' => (string)$evt['id']
                ];
            }
        }
    }
    // Sort by time
    usort($allSessions, function($a,$b){ return ($a['ts'] <=> $b['ts']); });
}
?>

<div class="font-['Open_Sans'] bg-gray-50 w-screen relative left-1/2 -translate-x-1/2 -mt-12">

    <?php 
        $heroImage = '/eventsphere/assets/images/hero-bg.png';
        // Fallback gradient if image missing, or verify path
        $heroBgStyle = 'background-image: linear-gradient(to bottom, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url(' . htmlspecialchars($heroImage) . ');';
    ?>
    <section style="<?php echo $heroBgStyle; ?> background-size: cover; background-position: center;" class="relative text-white min-h-[600px] flex items-center">
        <div class="max-w-7xl mx-auto py-20 px-6 lg:px-8 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div>
                    <h1 class="text-4xl sm:text-5xl font-extrabold font-['Montserrat'] tracking-tight text-white leading-tight">
                        The Future of <br/><span class="text-indigo-400">Event Management</span> is Here.
                    </h1>
                    <p class="mt-6 text-lg text-slate-300 max-w-xl leading-relaxed">
                        Seamless ticketing, interactive venue maps, and real-time connection. Experience the next generation of event tech with EventSphere+.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="/eventsphere/schedule.php" class="inline-flex items-center gap-2 px-8 py-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 text-white font-bold transition-transform hover:-translate-y-1">
                            <i data-lucide="ticket" class="w-5 h-5"></i> Get Tickets
                        </a>
                        <a href="#schedule" class="inline-flex items-center gap-2 px-6 py-4 rounded-lg border border-slate-600 text-slate-200 hover:bg-white/10 hover:text-white transition-colors font-semibold">
                            View Agenda
                        </a>
                    </div>
                </div>

                <div class="hidden lg:block relative">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-slate-800/50 backdrop-blur-md border border-white/10 transform rotate-1 hover:rotate-0 transition-transform duration-500">
                        <div class="bg-slate-900/80 px-4 py-3 flex items-center gap-2 border-b border-white/5">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        </div>
                        
                        <div class="p-8">
                            <h3 class="text-white text-xl font-bold font-['Montserrat']">EventSphere+ Dashboard</h3>
                            <p class="text-slate-400 mt-2 text-sm">Real-time metrics & management.</p>
                            
                            <div class="mt-6 grid grid-cols-2 gap-4">
                                <div class="bg-slate-900/60 p-4 rounded-lg border border-white/5">
                                    <div class="text-indigo-400 text-xs font-bold uppercase tracking-wider mb-1">Next Event</div>
                                    <?php if ($upcomingEvent): ?>
                                        <div class="text-white font-bold truncate"><?php echo htmlspecialchars((string)$upcomingEvent->title); ?></div>
                                        <div class="text-slate-400 text-xs mt-1"><?php echo date('M j, Y', strtotime((string)$upcomingEvent->date)); ?></div>
                                    <?php else: ?>
                                        <div class="text-slate-500 italic">No events scheduled</div>
                                    <?php endif; ?>
                                </div>
                                <div class="bg-slate-900/60 p-4 rounded-lg border border-white/5">
                                    <div class="text-teal-400 text-xs font-bold uppercase tracking-wider mb-1">Total Sales</div>
                                    <div class="text-white font-bold text-2xl"><?php echo number_format((int)$ticketsSoldCount); ?></div>
                                    <div class="text-slate-400 text-xs">Tickets Sold</div>
                                </div>
                            </div>

                            <div class="mt-4 bg-indigo-900/30 border border-indigo-500/30 p-4 rounded-lg flex items-center justify-center h-24 text-indigo-200 text-sm">
                                <i data-lucide="map" class="w-5 h-5 mr-2"></i> Interactive Venue Map Loaded
                            </div>
                        </div>
                    </div>
                    
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-teal-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 1s;"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl font-extrabold font-['Montserrat'] text-slate-900 sm:text-4xl">Everything you need</h2>
                <p class="mt-4 text-lg text-slate-600">EventSphere+ brings together all the tools you need for modern events into one tech-forward platform.</p>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 border border-slate-100 bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow duration-300">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-6">
                        <i data-lucide="ticket" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold font-['Montserrat'] text-slate-900">XML-Based Ticketing</h3>
                    <p class="mt-3 text-slate-600 leading-relaxed">Fast, portable, and secure. Our XML architecture ensures your data is lightweight and easy to backup without complex SQL servers.</p>
                </div>

                <div class="p-8 border border-slate-100 bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow duration-300">
                    <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center mb-6">
                        <i data-lucide="map" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold font-['Montserrat'] text-slate-900">Interactive Venue Map</h3>
                    <p class="mt-3 text-slate-600 leading-relaxed">Navigate with ease. Attendees can view booth locations, session rooms, and amenities on a responsive SVG map.</p>
                </div>

                <div class="p-8 border border-slate-100 bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow duration-300">
                    <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center mb-6">
                        <i data-lucide="message-circle" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold font-['Montserrat'] text-slate-900">Real-Time Networking</h3>
                    <p class="mt-3 text-slate-600 leading-relaxed">Foster connections. Our built-in chat system allows attendees to network and get live support instantly.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="venue" class="py-24 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1 relative">
                    <div class="bg-white p-2 rounded-xl shadow-lg transform -rotate-1">
                        <div class="bg-slate-100 rounded-lg aspect-video flex items-center justify-center border-2 border-dashed border-slate-300 relative overflow-hidden group">
                            <div class="absolute inset-0 bg-[url('/eventsphere/assets/images/map-pattern.svg')] opacity-10"></div>
                            <div class="text-center z-10">
                                <i data-lucide="map-pin" class="w-12 h-12 text-slate-400 mx-auto mb-2 group-hover:text-indigo-500 transition-colors"></i>
                                <span class="text-slate-500 font-semibold">Interactive Floor Plan</span>
                            </div>
                            
                            <div class="absolute top-1/4 left-1/4 w-3 h-3 bg-indigo-500 rounded-full animate-ping"></div>
                            <div class="absolute bottom-1/3 right-1/4 w-3 h-3 bg-teal-500 rounded-full animate-ping" style="animation-delay: 0.5s"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <h2 class="text-3xl font-extrabold font-['Montserrat'] text-slate-900">Navigate like a Pro</h2>
                    <p class="mt-4 text-lg text-slate-600">Never get lost again. Our dynamic venue map helps you find exactly what you're looking for.</p>
                    
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-green-600"></i>
                            </div>
                            <span class="text-slate-700">Locate session rooms instantly</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-green-600"></i>
                            </div>
                            <span class="text-slate-700">Find exhibitor booths & amenities</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-green-600"></i>
                            </div>
                            <span class="text-slate-700">Real-time crowd heatmaps (Pro)</span>
                        </li>
                    </ul>

                    <div class="mt-8">
                        <a href="/eventsphere/venue.php" class="text-indigo-600 font-semibold hover:text-indigo-800 flex items-center gap-2">
                            Explore the Venue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="schedule" class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold font-['Montserrat'] text-slate-900">Event Agenda</h2>
                <p class="mt-2 text-slate-600">A sneak peek at what's coming up.</p>
            </div>

            <div class="relative border-l-2 border-indigo-100 ml-4 space-y-12">
                <?php if (!empty($allSessions)): ?>
                    <?php $limit = 5; $count = 0; foreach ($allSessions as $s): if ($count++ >= $limit) break; ?>
                        <?php $st = (int)$s['ts']; $stime = $s['time'] ?: ($st ? date('g:i A', $st) : ''); ?>
                        <div class="relative pl-8 group">
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-4 border-indigo-600 group-hover:scale-125 transition-transform"></div>
                            
                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-2 mb-1">
                                <span class="text-sm font-bold text-indigo-600 uppercase tracking-wide"><?php echo htmlspecialchars($stime); ?></span>
                                <h3 class="text-xl font-bold font-['Montserrat'] text-slate-900"><?php echo htmlspecialchars($s['title']); ?></h3>
                            </div>
                            
                            <div class="text-slate-600 mb-2">
                                <?php if ($s['speaker']): ?>
                                    <span class="font-medium text-slate-800"><?php echo htmlspecialchars($s['speaker']); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($s['eventTitle']): ?>
                            <div class="text-sm text-slate-400">
                                Part of <a href="/eventsphere/event-details.php?id=<?php echo urlencode((string)$s['eventId']); ?>" class="hover:text-indigo-600 hover:underline"><?php echo htmlspecialchars($s['eventTitle']); ?></a>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="pl-8 pt-4">
                        <a href="/eventsphere/schedule.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 font-medium transition-all">
                            View Full Schedule <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                <?php else: ?>
                    <div class="pl-8">
                        <p class="text-slate-500 italic">No public sessions scheduled yet. Check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

   
<?php require_once __DIR__ . '/templates/footer.php'; ?>
<!-- Lucide Icons script + createIcons moved to /templates/footer.php for consistent loading -->