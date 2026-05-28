// Confirmation Modal System
let confirmCallback = null;
const modal = document.getElementById('confirmationModal');
const modalTitle = document.getElementById('modalTitle');
const modalMessage = document.getElementById('modalMessage');
const confirmButton = document.getElementById('confirmButton');
const cancelButton = document.getElementById('cancelButton');

// Show modal function
function showConfirmation(title, message, callback) {
  modalTitle.textContent = title;
  modalMessage.textContent = message;
  confirmCallback = callback;
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

// Hide modal function
function hideConfirmation() {
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

// Confirm button click
confirmButton.addEventListener('click', function() {
  hideConfirmation();
  if (confirmCallback) {
    confirmCallback(true);
    confirmCallback = null;
  }
});

// Cancel button click
cancelButton.addEventListener('click', function() {
  hideConfirmation();
  if (confirmCallback) {
    confirmCallback(false);
    confirmCallback = null;
  }
});

// Close modal when clicking outside
modal.addEventListener('click', function(e) {
  if (e.target === modal) {
    hideConfirmation();
    if (confirmCallback) {
      confirmCallback(false);
      confirmCallback = null;
    }
  }
});

// Replace default confirm dialogs
function confirmAction(event, title = '¿Estás seguro?', message = 'Esta acción no se puede deshacer.') {
  event.preventDefault();
  const form = event.target.closest('form');
  
  showConfirmation(title, message, (confirmed) => {
    if (confirmed && form) {
      form.submit();
    }
  });
}
