<?php
// templates/sidebar.php - reusable sidebar for user pages
require_once __DIR__ . '/../core/xml_handler.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$userId = $_SESSION['user_id'] ?? null;
// Ensure $currentUser is available or try to load it
if (!isset($currentUser) && $userId) {
    $usersXml = new XmlHandler(__DIR__ . '/../data/users.xml');
    $users = $usersXml->read();
    if ($users && $users->user) {
        foreach ($users->user as $u) {
            if ((string)$u['id'] === (string)$userId) { $currentUser = $u; break; }
        }
    }
}
?>
<aside id="sidebar" class="w-72 bg-white border-r border-gray-200 transform transition-transform duration-200 -translate-x-full md:translate-x-0 md:flex flex-col z-20">
    <div class="p-6">
        <div class="text-2xl font-bold font-['Montserrat'] text-indigo-600 tracking-tight">EventSphere<span class="text-slate-900">+</span></div>
    </div>
    <div class="px-6 pt-2 pb-4 border-b">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center">
                <?php
                    $avatarPath = '/eventsphere/assets/images/avatar-placeholder.svg';
                    if (isset($currentUser->avatar) && (string)$currentUser->avatar) {
                        $avatarPath = '/eventsphere/' . ltrim((string)$currentUser->avatar, '/');
                    } else {
                        $possible = glob(__DIR__ . '/../uploads/avatars/' . (string)$userId . '.*');
                        if ($possible && count($possible)) {
                            $avatarPath = '/eventsphere/uploads/avatars/' . basename($possible[0]);
                        }
                    }
                ?>
                <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="Avatar" class="w-full h-full object-cover" />
            </div>
            <div class="text-sm">
                <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($_SESSION['name'] ?? ($_SESSION['username'] ?? 'Attendee')); ?></div>
                <div class="text-slate-400 text-xs"><?php echo htmlspecialchars((string)($currentUser->role ?? $_SESSION['role'] ?? 'Attendee')); ?></div>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-2 mt-4 overflow-y-auto">
        <a href="/eventsphere/dashboard.php" class="<?php echo navClasses('dashboard.php', '', 'sidebar'); ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5 <?php echo iconClasses('dashboard.php', 'sidebar'); ?>"></i> Dashboard
        </a>
        <a href="/eventsphere/my-tickets.php" class="<?php echo navClasses('my-tickets.php', '', 'sidebar'); ?>">
            <i data-lucide="ticket" class="w-5 h-5 <?php echo iconClasses('my-tickets.php', 'sidebar'); ?>"></i> My Tickets
        </a>
        <a href="/eventsphere/schedule.php" class="<?php echo navClasses('schedule.php', '', 'sidebar'); ?>">
            <i data-lucide="calendar-days" class="w-5 h-5 <?php echo iconClasses('schedule.php', 'sidebar'); ?>"></i> Event Schedule
        </a>
        <a href="/eventsphere/venue.php" class="<?php echo navClasses('venue.php', '', 'sidebar'); ?>">
            <i data-lucide="map" class="w-5 h-5 <?php echo iconClasses('venue.php', 'sidebar'); ?>"></i> Venue Map
        </a>
        <a href="/eventsphere/chat.php" class="<?php echo navClasses('chat.php', '', 'sidebar'); ?>">
            <i data-lucide="message-circle" class="w-5 h-5 <?php echo iconClasses('chat.php', 'sidebar'); ?>"></i> Networking
        </a>
        <a href="/eventsphere/settings.php" class="<?php echo navClasses('settings.php', '', 'sidebar'); ?>">
            <i data-lucide="settings" class="w-5 h-5 <?php echo iconClasses('settings.php', 'sidebar'); ?>"></i> Settings
        </a>
    </nav>

    <div class="p-6 border-t mt-auto">
        <a href="/eventsphere/logout.php" class="block w-full text-left text-red-600 hover:bg-red-600 hover:text-white px-4 py-3 rounded">Logout</a>
    </div>
</aside>

<!-- Sidebar backdrop for mobile -->
<div id="sidebarBackdrop" class="fixed inset-0 bg-black/30 hidden z-30 md:hidden"></div>

<!-- Mobile Sidebar: displayed for small screens only, togglable via JS -->
<div id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl z-30 transform -translate-x-full transition-transform duration-300 md:hidden">
    <div class="p-6 border-b">
        <span class="text-xl font-bold text-indigo-600">EventSphere+</span>
    </div>
    <nav class="p-4 space-y-2">
        <?php // replicate sidebar nav for mobile: use same navClasses with sidebar variant ?>
        <a href="/eventsphere/dashboard.php" class="<?php echo navClasses('dashboard.php', 'text-slate-300', 'sidebar'); ?>"> <i data-lucide="layout-dashboard" class="w-5 h-5 <?php echo iconClasses('dashboard.php', 'sidebar', 'text-slate-300'); ?>"></i> Dashboard</a>
        <a href="/eventsphere/my-tickets.php" class="<?php echo navClasses('my-tickets.php', 'text-slate-300', 'sidebar'); ?>"> <i data-lucide="ticket" class="w-5 h-5 <?php echo iconClasses('my-tickets.php', 'sidebar', 'text-slate-300'); ?>"></i> My Tickets</a>
        <a href="/eventsphere/schedule.php" class="<?php echo navClasses('schedule.php', 'text-slate-300', 'sidebar'); ?>"> <i data-lucide="calendar-days" class="w-5 h-5 <?php echo iconClasses('schedule.php', 'sidebar', 'text-slate-300'); ?>"></i> Event Schedule</a>
        <a href="/eventsphere/venue.php" class="<?php echo navClasses('venue.php', 'text-slate-300', 'sidebar'); ?>"> <i data-lucide="map" class="w-5 h-5 <?php echo iconClasses('venue.php', 'sidebar', 'text-slate-300'); ?>"></i> Venue Map</a>
        <a href="/eventsphere/chat.php" class="<?php echo navClasses('chat.php', 'text-slate-300', 'sidebar'); ?>"> <i data-lucide="message-circle" class="w-5 h-5 <?php echo iconClasses('chat.php', 'sidebar', 'text-slate-300'); ?>"></i> Networking</a>
    </nav>
</div>

<?php
// End of sidebar.php
?>
