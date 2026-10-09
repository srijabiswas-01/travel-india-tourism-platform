document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.inline-workflow').forEach((form) => {
    const bookingId = form.querySelector('[name="id"]')?.value;
    const noteInput = form.querySelector('input[name="admin_note"]');
    const row = form.closest('tr');
    const reference = row?.querySelector('td:first-child strong');

    if (bookingId && reference) {
      const link = document.createElement('a');
      link.className = 'booking-details-link';
      link.href = `booking-details.php?id=${encodeURIComponent(bookingId)}`;
      link.title = 'View full booking details';
      link.setAttribute('aria-label', `View booking ${reference.textContent.trim()}`);
      link.innerHTML = '<i class="fa-solid fa-eye" aria-hidden="true"></i>';
      reference.insertAdjacentElement('afterend', link);
    }

    if (noteInput) {
      const noteField = document.createElement('textarea');
      noteField.name = noteInput.name;
      noteField.value = noteInput.value;
      noteField.placeholder = 'Customer update (required for information/cancellation)';
      noteField.maxLength = 1000;
      noteField.rows = 2;
      noteField.setAttribute('aria-label', 'Customer update');
      noteInput.replaceWith(noteField);
    }
  });
});