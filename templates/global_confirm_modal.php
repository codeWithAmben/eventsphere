<?php
/**
 * templates/global_confirm_modal.php
 * Reusable global confirm modal, includes a form with CSRF to submit actions that require confirmation.
 */
?>
<div id="globalConfirmModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50" aria-hidden="true">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
    <div class="flex items-start justify-between">
      <div>
        <h3 id="confirm-title" class="text-lg font-montserrat font-bold text-slate-800">Confirm</h3>
        <p id="confirm-message" class="mt-2 text-sm text-slate-600">Are you sure you want to continue?</p>
      </div>
      <div>
        <button type="button" onclick="closeConfirmModal()" class="text-slate-500 hover:text-slate-700"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>
    </div>
    <form id="confirm-form" method="post" class="mt-6">
      <?php echo csrf_input_field(); ?>
      <!-- Include both for compatibility with different action endpoints -->
      <input type="hidden" name="id" id="confirm-id" value="" />
      <input type="hidden" name="event_id" id="confirm-event-id" value="" />
      <div class="mt-4 flex items-center justify-end gap-3">
        <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 rounded border bg-slate-100 text-slate-700">Cancel</button>
        <button id="confirm-button" type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white">Confirm</button>
      </div>
    </form>
  </div>
</div>
