<?php
require_once __DIR__ . '/../core/env.php';
require_once __DIR__ . '/../core/csrf.php';
/**
 * header.php - Common header snippets
 */
?>
<!doctype html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>EventSphere+</title>
        <!-- Google Fonts: Montserrat (headings) & Open Sans (body) -->
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
        <!-- Tailwind CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Removed FontAwesome; using Lucide Icons and Google Fonts per design system -->
        <link rel="stylesheet" href="/eventsphere/assets/css/main.css">
        <style>
            /* Typography system */
            body { font-family: 'Open Sans', sans-serif; }
            h1, h2, h3, h4 { font-family: 'Montserrat', sans-serif; font-weight: 700; }
            /* Small helper classes to ease Tailwind usage if needed */
            .font-montserrat { font-family: 'Montserrat', sans-serif; }
            .font-opensans { font-family: 'Open Sans', sans-serif; }
        </style>
</head>
<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<?php
// Active page detection for nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/helpers.php';
?>
<body class="antialiased bg-slate-50 text-slate-800">
    <?php if (!($GLOBALS['hideGlobalHeader'] ?? false)): ?>
    <header class="bg-slate-900 text-white sticky top-0 z-40 shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="/eventsphere/index.php" class="flex items-center gap-2">
                        <div class="text-2xl font-extrabold text-indigo-400">EventSphere<span class="text-teal-400">+</span></div>
                    </a>
                </div>
                <nav class="hidden md:flex items-center gap-6">
                    <a href="/eventsphere/index.php" class="<?php echo navClasses('index.php'); ?>">Home</a>
                    <a href="/eventsphere/schedule.php" class="<?php echo navClasses('schedule.php'); ?>">Schedule</a>
                    <a href="/eventsphere/venue.php" class="<?php echo navClasses('venue.php'); ?>">Venue</a>
                    <a href="/eventsphere/index.php#contact" class="<?php echo navClasses('index.php'); ?>">Contact</a>
                </nav>
                <div class="hidden md:flex items-center gap-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php $role = strtolower(trim((string)($_SESSION['role'] ?? 'attendee'))); ?>
                        <?php if ($role === 'admin'): ?>
                          <a href="/eventsphere/admin.php?tab=overview" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white text-sm font-montserrat">Admin Panel</a>
                          <a href="/eventsphere/logout.php" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-slate-800/30 hover:bg-slate-800/20 border border-slate-700 text-sm text-white">Logout</a>
                        <?php else: ?>
                          <a href="/eventsphere/dashboard.php" class="px-3 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-sm">My Dashboard</a>
                          <a href="/eventsphere/logout.php" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-slate-800/30 hover:bg-slate-800/20 border border-slate-700 text-sm text-white" title="Logout"><i data-lucide="log-out" class="w-4 h-4"></i></a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/eventsphere/login.php" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-slate-800/30 hover:bg-slate-800/20 border border-slate-700 text-sm"> 
                            <i data-lucide="log-in" class="w-4 h-4 text-white"></i>
                            <span class="text-white font-opensans">Login</span>
                        </a>
                        <a href="/eventsphere/register.php" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-opensans"> 
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>Register</span>
                        </a>
                    <?php endif; ?>
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobileMenuButton" class="p-2 rounded-md inline-flex items-center justify-center text-slate-200 hover:text-white hover:bg-slate-800/30 focus:outline-none" aria-label="Open menu" aria-expanded="false" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
                        <svg id="hamburgerOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="hamburgerClose" xmlns="http://www.w3.org/2000/svg" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-slate-900/95">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="#features" class="block px-3 py-2 rounded-md text-base font-medium text-slate-200">Features</a>
                    <a href="#schedule" class="block px-3 py-2 rounded-md text-base font-medium text-slate-200">Schedule</a>
                    <a href="#venue" class="block px-3 py-2 rounded-md text-base font-medium text-slate-200">Venue</a>
                    <a href="#contact" class="block px-3 py-2 rounded-md text-base font-medium text-slate-200">Contact</a>
                    <div class="pt-3 pb-3 border-t border-slate-800 text-center">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="/eventsphere/logout.php" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-slate-800/30 text-slate-200">Logout</a>
                        <?php else: ?>
                            <a href="/eventsphere/login.php" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-slate-800/30 text-slate-200">Login</a>
                            <a href="/eventsphere/register.php" class="ml-2 inline-flex items-center gap-2 px-4 py-2 rounded bg-indigo-600 text-white">Register</a>
                        <?php endif; ?>
                    </div>
            </div>
        </div>
    </header>
    <?php endif; ?>
    <main>
