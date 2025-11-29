<?php
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/xml_handler.php';

// Security & Role Check
$auth = new Auth();
$auth->requireLogin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: /eventsphere/dashboard.php');
    exit();
}

// Hide global header for custom layout
$GLOBALS['hideGlobalHeader'] = true;
require_once __DIR__ . '/templates/header.php';

// Determine initial active tab server-side for non-JS fallback
$activeTab = strtolower(trim((string)($_GET['tab'] ?? 'overview')));
$allowedTabs = ['overview', 'events', 'attendees'];
if (!in_array($activeTab, $allowedTabs)) $activeTab = 'overview';

// Load Data
$eventsXml = (new XmlHandler(__DIR__ . '/data/events.xml'))->read();
$ticketsXml = (new XmlHandler(__DIR__ . '/data/tickets.xml'))->read();
$usersXml = (new XmlHandler(__DIR__ . '/data/users.xml'))->read();

// Backwards-compatible aliases: other templates and logic expect $events, $tickets, $users
$events = $eventsXml;
$tickets = $ticketsXml;
$users = $usersXml;

// Stats Calculation
$eventsCount = ($events && $events->event) ? count($events->event) : 0;
$ticketsCount = ($tickets && $tickets->ticket) ? count($tickets->ticket) : 0;
$usersCount = ($users && $users->user) ? count($users->user) : 0;

$totalRevenue = 0.00;
if ($tickets && $tickets->ticket) {
    foreach ($tickets->ticket as $tk) {
        if (isset($tk->price)) $totalRevenue += (float)$tk->price;
    }
}
?>

<div class="flex h-screen overflow-hidden bg-gray-50 font-['Open_Sans']">

    <aside id="sidebar" class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col z-20">
        <div class="p-6">
            <div class="text-2xl font-bold font-['Montserrat'] text-indigo-600">
                EventSphere<span class="text-slate-900">+</span>
            </div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Admin Panel</div>
        </div>
        
        <nav class="flex-1 px-4 space-y-1 mt-2">
            <!-- Link to the regular user dashboard -->
            <a href="/eventsphere/dashboard.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> View Dashboard
            </a>
            <?php $overviewActiveClass = ($activeTab === 'overview') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?>
            <a href="/eventsphere/admin.php?tab=overview" data-tab="overview" id="nav-overview" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $overviewActiveClass ?>" <?= ($activeTab === 'overview' ? 'aria-current="page"' : '') ?>>
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <?php $eventsActiveClass = ($activeTab === 'events') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?>
            <a href="/eventsphere/admin.php?tab=events" data-tab="events" id="nav-events" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $eventsActiveClass ?>" <?= ($activeTab === 'events' ? 'aria-current="page"' : '') ?>>
                <i data-lucide="calendar" class="w-5 h-5"></i> Events
            </a>
            <?php $attendeesActiveClass = ($activeTab === 'attendees') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?>
            <a href="/eventsphere/admin.php?tab=attendees" data-tab="attendees" id="nav-attendees" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= $attendeesActiveClass ?>" <?= ($activeTab === 'attendees' ? 'aria-current="page"' : '') ?>>
                <i data-lucide="users" class="w-5 h-5"></i> Attendees
            </a>
        </nav>

        <div class="p-4 border-t border-gray-100">
            <a href="/eventsphere/logout.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                <i data-lucide="log-out" class="w-5 h-5"></i> Logout
            </a>
        </div>
    </aside>

    <div id="sidebarBackdrop" class="fixed inset-0 bg-black/50 z-20 hidden md:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 overflow-y-auto p-8 relative">
        
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-slate-900" id="pageTitle">Dashboard Overview</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-900">Administrator</div>
                    <div class="text-xs text-slate-500">Super User</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold border border-indigo-200">
                    A
                </div>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-emerald-50 text-emermakald-700 border border-emerald-200 flex items-center gap-2 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span>
                    <?php 
                        $msgs = [
                            'event_saved' => 'Event saved successfully.',
                            'deleted' => 'Event deleted successfully.',
                            'success' => 'Announcement posted.',
                        ];
                        echo htmlspecialchars($msgs[$_GET['msg']] ?? $_GET['msg']); 
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <div id="tab-overview" class="tab-content <?= ($activeTab === 'overview') ? 'block' : 'hidden' ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Revenue</span>
                    <span class="text-2xl font-bold text-slate-900 mt-2">$<?php echo number_format($totalRevenue, 2); ?></span>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tickets Sold</span>
                    <span class="text-2xl font-bold text-slate-900 mt-2"><?php echo (int)$ticketsCount; ?></span>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Events</span>
                    <span class="text-2xl font-bold text-slate-900 mt-2"><?php echo (int)$eventsCount; ?></span>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Users</span>
                    <span class="text-2xl font-bold text-slate-900 mt-2"><?php echo (int)$usersCount; ?></span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Post Global Announcement</h3>
                    <form method="post" action="/eventsphere/admin_post_announcement.php">
                        <?php if (function_exists('csrf_input_field')) echo csrf_input_field(); ?>
                        <textarea name="text" rows="3" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3" placeholder="Type your message here..."></textarea>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm">
                                Post Update
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center items-center text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="bar-chart-2" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <h3 class="text-slate-900 font-medium">Sales Analytics</h3>
                    <p class="text-slate-500 text-sm mt-1">Chart integration coming soon.</p>
                </div>
            </div>
        </div>

        <div id="tab-events" class="tab-content <?= ($activeTab === 'events') ? 'block' : 'hidden' ?>">
            <div class="flex items-center justify-between mb-6 gap-3">
                <div class="flex items-center gap-3">
                    <label class="text-sm text-slate-600">Filter:</label>
                    <select id="eventsFilter" class="text-sm rounded border-gray-200 px-3 py-1" onchange="filterEvents()">
                        <option value="all">All</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Live Now">Live Now</option>
                        <option value="Delayed">Delayed</option>
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <input id="eventsSearch" placeholder="Search events..." class="rounded border-gray-200 px-3 py-1 text-sm" oninput="filterEvents()">
                </div>
            </div>
            <div class="flex justify-end mb-6">
                <button onclick="openAddEventModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Event
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-slate-500 font-medium border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3">Event Name</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Venue</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($events && $events->event): foreach ($events->event as $evt): ?>
                        <?php $statusStr = (string)($evt->status ?? ''); ?>
                        <tr class="hover:bg-gray-50 transition-colors group" data-status="<?php echo htmlspecialchars($statusStr); ?>">
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <?php echo htmlspecialchars((string)$evt->title); ?>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">ID: <?php echo htmlspecialchars((string)$evt['id']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-slate-500"><?php echo htmlspecialchars((string)$evt->date); ?></td>
                            <td class="px-6 py-4 text-slate-500"><?php echo htmlspecialchars((string)$evt->venue); ?></td>
                            <td class="px-6 py-4">
                                <form method="post" action="/eventsphere/admin_set_event_status.php" class="inline-block">
                                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars((string)$evt['id']); ?>">
                                    <select name="status" onchange="this.form.submit()" class="text-xs rounded-full border-gray-200 bg-slate-100 text-slate-700 px-3 py-1 focus:ring-0 cursor-pointer">
                                        <option value="Scheduled" <?php echo ((string)($evt->status ?? '')==='Scheduled')?'selected':''; ?>>Scheduled</option>
                                        <option value="Live Now" <?php echo ((string)($evt->status ?? '')==='Live Now')?'selected':''; ?>>Live Now</option>
                                        <option value="Delayed" <?php echo ((string)($evt->status ?? '')==='Delayed')?'selected':''; ?>>Delayed</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                <button onclick="openEditEventModal('<?php echo htmlspecialchars((string)$evt['id']); ?>')" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="askConfirmation('Delete Event?', 'This cannot be undone.', '/eventsphere/admin_delete_event.php', '<?php echo htmlspecialchars((string)$evt['id']); ?>', true)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">No events found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-attendees" class="tab-content <?= ($activeTab === 'attendees') ? 'block' : 'hidden' ?>">
            <div class="flex items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-3">
                    <label class="text-sm text-slate-600">Search:</label>
                    <input id="attendeesSearch" placeholder="Search attendees..." class="rounded border-gray-200 px-3 py-1 text-sm" oninput="filterAttendees()" />
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="exportAttendeesCSV()" class="text-sm bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">Export CSV</button>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-slate-500 font-medium border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Auth Method</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($users && $users->user): foreach ($users->user as $u): ?>
                        <tr class="hover:bg-gray-50 transition-colors user-row" data-username="<?php echo htmlspecialchars((string)$u->username); ?>" data-email="<?php echo htmlspecialchars((string)$u->email); ?>" data-role="<?php echo htmlspecialchars((string)$u->role); ?>">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900"><?php echo htmlspecialchars((string)$u->username); ?></div>
                                <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string)$u->email); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <form method="post" action="/eventsphere/actions/admin_update_user.php">
                                    <?php if (function_exists('csrf_input_field')) echo csrf_input_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars((string)$u['id']); ?>" />
                                    <select name="role" onchange="this.form.submit()" class="rounded text-sm border-gray-200 px-3 py-1">
                                        <option value="attendee" <?php echo ((string)$u->role === 'attendee') ? 'selected' : ''; ?>>Attendee</option>
                                        <option value="admin" <?php echo ((string)$u->role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                <?php echo htmlspecialchars((string)$u->sso ?? 'Email'); ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-slate-400 hover:text-indigo-600 transition-colors">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<div id="addEventModal" class="fixed inset-0 hidden z-50 overflow-y-auto" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeAddEventModal()"></div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-6 py-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold font-['Montserrat'] text-slate-900" id="modalTitle">Create New Event</h3>
                    <button onclick="closeAddEventModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                
                <form id="addEventForm" action="/eventsphere/actions/save_event.php" method="POST">
                    <input type="hidden" name="id" id="event_id_hidden">
                    <?php if (function_exists('csrf_input_field')) echo csrf_input_field(); ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Title</label>
                            <input type="text" placeholder="Add your event title here." id="event_title" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Date & Time</label>
                            <input type="datetime-local" name="date" id="event_date" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Venue</label>
                        <input type="text" name="venue" placeholder="event_venue" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                        <textarea name="description" placeholder="Event description"id="event_description" rows="3" class="w-full border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="space-y-6 border-t border-gray-100 pt-6">
                        
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wide">Schedule</h4>
                                <button type="button" onclick="addScheduleRow()" class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1 rounded hover:bg-indigo-100 transition-colors">+ Add Slot</button>
                            </div>
                            <div id="scheduleContainer" class="space-y-3"></div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wide">Speakers</h4>
                                <button type="button" onclick="addSpeakerRow()" class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1 rounded hover:bg-indigo-100 transition-colors">+ Add Speaker</button>
                            </div>
                            <div id="speakersContainer" class="space-y-3"></div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
                        <button type="button" onclick="closeAddEventModal()" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 rounded-lg text-sm font-bold text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5">Save Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Tab Switching Logic
    function switchAdminTab(tabName) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-' + tabName).classList.remove('hidden');
        
        // Update Sidebar Styles
        const navIds = ['overview', 'events', 'attendees'];
        navIds.forEach(id => {
            const btn = document.getElementById('nav-' + id);
            if (id === tabName) {
                btn.classList.remove('text-slate-600', 'hover:bg-slate-50');
                btn.classList.add('text-indigo-600', 'bg-indigo-50');
            } else {
                btn.classList.remove('text-indigo-600', 'bg-indigo-50');
                btn.classList.add('text-slate-600', 'hover:bg-slate-50');
            }
        });

        // Update Title
        const titles = { 'overview': 'Dashboard Overview', 'events': 'Event Management', 'attendees': 'User Management' };
        document.getElementById('pageTitle').innerText = titles[tabName];
        // Set aria-current on the active nav item for accessibility
        navIds.forEach(id => {
            const el = document.getElementById('nav-' + id);
            if (!el) return;
            if (id === tabName) el.setAttribute('aria-current', 'page');
            else el.removeAttribute('aria-current');
        });
        // Update browser URL (without reloading) so tab is shareable/bookmarkable
        if (history && history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            history.replaceState(null, '', url.pathname + '?' + url.searchParams.toString());
        }
    }

    // --- Attendees filter & export utilities
    function filterAttendees() {
        const term = document.getElementById('attendeesSearch').value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            const user = row.dataset.username.toLowerCase();
            const email = row.dataset.email.toLowerCase();
            const role = (row.dataset.role || '').toLowerCase();
            const matched = user.includes(term) || email.includes(term) || role.includes(term) || term === '';
            row.style.display = matched ? '' : 'none';
        });
    }

    function exportAttendeesCSV() {
        const rows = [['id','username','email','role']];
        document.querySelectorAll('.user-row').forEach(row => {
            if (row.style.display === 'none') return;
            const id = row.querySelector('input[name="user_id"]') ? row.querySelector('input[name="user_id"]').value : '';
            const username = row.dataset.username || '';
            const email = row.dataset.email || '';
            const role = row.dataset.role || '';
            rows.push([id, username, email, role]);
        });
        const csvContent = rows.map(r => r.map(c => '"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'attendees.csv';
        a.click();
        URL.revokeObjectURL(url);
    }

    // --- Events filter
    function filterEvents() {
        const term = document.getElementById('eventsSearch').value.toLowerCase();
        const status = document.getElementById('eventsFilter').value;
        document.querySelectorAll('#tab-events table tbody tr').forEach(row => {
            const title = (row.querySelector('td') ? row.querySelector('td').innerText : '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();
            const matchesTerm = term === '' || title.includes(term);
            const matchesStatus = status === 'all' || rowStatus === status.toLowerCase();
            row.style.display = (matchesTerm && matchesStatus) ? '' : 'none';
        });
    }

    // 2. Modal Logic
    const modal = document.getElementById('addEventModal');
    let scheduleCount = 0;
    let speakerCount = 0;

    function openAddEventModal() {
        document.getElementById('addEventForm').reset();
        document.getElementById('event_id_hidden').value = '';
        document.getElementById('modalTitle').innerText = 'Create New Event';
        document.getElementById('scheduleContainer').innerHTML = '';
        document.getElementById('speakersContainer').innerHTML = '';
        modal.classList.remove('hidden');
        
        // Add defaults
        addScheduleRow();
        addSpeakerRow();
    }

    function closeAddEventModal() {
        modal.classList.add('hidden');
    }

    // 3. Dynamic Rows Logic
    function addScheduleRow(time = '', title = '') {
        // Support both old signature addScheduleRow(time, title) and object init addScheduleRow({time, title})
        let t = time;
        let ttl = title;
        if (typeof time === 'object' && time !== null) {
            t = time.time || time.time || '';
            ttl = time.title || time.title || '';
        }
        const div = document.createElement('div');
        div.className = 'flex gap-3 items-center';
        div.innerHTML = `
            <input type="text" placeholder="e.g. 09:00 AM" name="schedule[${scheduleCount}][time]" value="${t}" class="border px-2 py-1 rounded w-28" />
            <input type="text" name="schedule[${scheduleCount}][title]" value="${ttl}" placeholder="Session Title" class="flex-1 border-gray-200 rounded-lg text-sm" />
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-red-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
        `;
        document.getElementById('scheduleContainer').appendChild(div);
        scheduleCount++;
        lucide.createIcons(); // Refresh icons for new row
    }

    function addSpeakerRow(name = '', role = '') {
        const div = document.createElement('div');
        div.className = 'flex gap-3 items-center';
        div.innerHTML = `
            <input type="text" name="speakers[${speakerCount}][name]" value="${name}" placeholder="Speaker Name" class="flex-1 border-gray-200 rounded-lg text-sm">
            <input type="text" name="speakers[${speakerCount}][role]" value="${role}" placeholder="Job Title" class="flex-1 border-gray-200 rounded-lg text-sm">
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-red-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
        `;
        document.getElementById('speakersContainer').appendChild(div);
        speakerCount++;
        lucide.createIcons();
    }

    // 4. Edit Logic (Mockup - Needs AJAX/JSON helper for real data)
    function openEditEventModal(id) {
        openAddEventModal();
        document.getElementById('modalTitle').innerText = 'Edit Event (ID: ' + id + ')';
        document.getElementById('event_id_hidden').value = id;
        // In a real app, fetch(get_event.php?id=...) here and populate fields
    }

    // Initialize
    lucide.createIcons();
    // If URL contains ?tab=, use that to switch to a particular tab on load.
    (function(){
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab') || 'overview';
        // Call switchAdminTab to set up the initial view
        switchAdminTab(tab);
    })();

    // When switching tabs, update the URL `?tab=` as well (so links are bookmarkable)
    const originalSwitch = switchAdminTab;
    // We can't easily override function, but we update history inside function below if needed.

        // Attach delegated click handlers for sidebar anchors that have `data-tab` so we
        // prevent the default navigation only when JS is up and running. This allows a
        // graceful fallback to the server-side `?tab=` redirect if JS is disabled.
        document.querySelectorAll('#sidebar a[data-tab]').forEach(a => {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                const tab = this.dataset.tab;
                if (tab) switchAdminTab(tab);
            });
        });
</script>

<?php include __DIR__ . '/templates/global_confirm_modal.php'; ?>
<?php require_once __DIR__ . '/templates/footer.php'; ?>