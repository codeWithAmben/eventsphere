<?php
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/xml_handler.php';
$GLOBALS['hideGlobalHeader'] = true;
require_once __DIR__ . '/templates/header.php';
$auth = new Auth();
$auth->requireLogin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$userId = $_SESSION['user_id'] ?? null;
?>
<div class="flex h-screen overflow-hidden bg-gray-50 font-['Open_Sans']">
    <?php require_once __DIR__ . '/templates/sidebar.php'; ?>
    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="w-full mx-auto bg-white p-4 md:p-6 rounded shadow max-w-none">
            <h1 class="text-2xl font-bold">Venue Map</h1>
            <p class="mt-4 text-slate-600">Interactive venue map coming soon. This is a placeholder page to host venue maps, exhibitor details, and related content.</p>
            <div class="mt-6 p-6 bg-slate-50 rounded-lg border border-slate-100">Placeholder for interactive map (SVG or canvas).</div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
