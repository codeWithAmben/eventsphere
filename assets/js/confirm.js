// assets/js/confirm.js
// Global confirmation modal controller
window.askConfirmation = function(title, message, actionUrl, id, isDestructive) {
    const modal = document.getElementById('globalConfirmModal');
    if (!modal) return;
    document.getElementById('confirm-title').textContent = title || 'Confirm';
    document.getElementById('confirm-message').textContent = message || '';
    const form = document.getElementById('confirm-form');
    form.action = actionUrl || '';
    const idField = document.getElementById('confirm-id');
    const eventField = document.getElementById('confirm-event-id');
    if (idField) idField.value = id || '';
    if (eventField) eventField.value = id || '';
    const confirmBtn = document.getElementById('confirm-button');
    // reset classes
    confirmBtn.classList.remove('bg-red-600','hover:bg-red-700','bg-indigo-600','hover:bg-indigo-700');
    if (isDestructive) {
        confirmBtn.classList.add('bg-red-600','hover:bg-red-700');
    } else {
        confirmBtn.classList.add('bg-indigo-600','hover:bg-indigo-700');
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
};

window.closeConfirmModal = function() {
    const modal = document.getElementById('globalConfirmModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
};

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('globalConfirmModal');
    if (!modal) return;
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeConfirmModal();
    });
});
