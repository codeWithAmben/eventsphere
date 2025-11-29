<?php
/**
 * footer.php - Minimalist Footer for EventSphere
 */
?>
</main> <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 py-8 font-['Open_Sans'] mt-auto">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
        
        <div class="text-sm">
            &copy; <?php echo date('Y'); ?> <span class="text-white font-bold font-['Montserrat']">EventSphere<span class="text-indigo-500">+</span></span>. All rights reserved.
        </div>

        <div class="flex items-center gap-6">
            <a href="#" class="hover:text-white transition-colors"><i data-lucide="twitter" class="w-4 h-4"></i></a>
            <a href="#" class="hover:text-white transition-colors"><i data-lucide="github" class="w-4 h-4"></i></a>
            <a href="#" class="hover:text-white transition-colors"><i data-lucide="linkedin" class="w-4 h-4"></i></a>
            <span class="text-slate-700">|</span>
            <a href="#" class="text-xs hover:text-white transition-colors">Privacy</a>
            <a href="#" class="text-xs hover:text-white transition-colors">Terms</a>
        </div>
    </div>
</footer>

<div id="exitIntentModal" class="fixed inset-0 hidden z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/80 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i data-lucide="bell" class="h-6 w-6 text-indigo-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 font-['Montserrat']" id="modal-title">Wait! Don't miss updates</h3>
                    <div class="mt-2">
                        <p class="text-sm text-slate-500">Get notified about flash sales and VIP upgrades. Subscribe to our newsletter.</p>
                        <form class="mt-4 flex gap-2" onsubmit="event.preventDefault(); closeExitModal(); alert('Subscribed!');">
                            <input type="email" placeholder="Email address" class="flex-1 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700">Join</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeExitModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                    No thanks
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/eventsphere/assets/js/main.js"></script>
<script src="/eventsphere/assets/js/confirm.js"></script>

<script src="https://unpkg.com/lucide@latest"></script>

<script>
    // Initialize Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Exit Intent Logic
    const exitModal = document.getElementById('exitIntentModal');
    let hasShownExitModal = false;

    // Trigger when mouse leaves top of window
    document.addEventListener('mouseleave', function(e) {
        if (e.clientY < 0 && !hasShownExitModal) {
            exitModal.classList.remove('hidden');
            hasShownExitModal = true;
        }
    });

    function closeExitModal() {
        exitModal.classList.add('hidden');
    }
</script>

</body>
</html>