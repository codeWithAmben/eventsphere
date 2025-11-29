<?php
// helpers.php - shared UI helpers for templates

if (!function_exists('navClasses')) {
    function navClasses($page, $extra = '', $variant = 'auto') {
        global $currentPage;
        $isActive = ($currentPage === $page);
        // Sidebar variant: darker look and active left border
        if ($variant === 'sidebar') {
            // Wider padding, full-width links for sidebar
            // Make *inactive* sidebar links a readable dark color (`text-slate-900`) while
            // keeping the active styles and hover states intact.
            $base = "flex items-center gap-3 px-4 py-3 rounded-md w-full text-left $extra";
            if ($isActive) return trim($base . ' bg-slate-800 text-white border-l-4 border-indigo-500');
            // If the caller passed an explicit text-* class via $extra, respect that instead
            // of forcing `text-slate-900` here. This allows mobile sidebar to keep a lighter
            // color while desktop uses the darker default.
            $hasExplicitTextClass = preg_match('/\btext-[^\s]+\b/', (string)$extra);
            if ($hasExplicitTextClass) {
                return trim($base . ' hover:bg-slate-100');
            }
            return trim($base . ' text-slate-900 hover:bg-slate-100 hover:text-slate-900');
        }
        // Default: header / inline nav
        if ($isActive) return trim("text-indigo-600 font-bold $extra");
        return trim("text-slate-200 hover:text-white $extra");
    }
}
if (!function_exists('iconClasses')) {
    function iconClasses($page, $variant = 'auto', $extra = '') {
        global $currentPage;
        $isActive = ($currentPage === $page);
        if ($variant === 'sidebar') {
            // Sidebar icons: active icons stay white (for dark bg); inactive icons should be
            // darker to match link text and improve contrast on white background.
            // If caller provided a color in $extra, respect it and don't override.
            $hasExplicitText = preg_match('/\btext-[^\s]+\b/', (string)$extra);
            if ($isActive) return 'text-white';
            return $hasExplicitText ? (string)$extra : 'text-slate-900';
        }
        // Default header/content icons: active can be indigo, inactive should be darker
        // so they match the surrounding text when not hovered.
        $hasExplicitText = preg_match('/\btext-[^\s]+\b/', (string)$extra);
        if ($isActive) return 'text-indigo-300';
        return $hasExplicitText ? (string)$extra : 'text-slate-900';
    }
}
