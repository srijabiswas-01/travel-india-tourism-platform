document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.detailed-trip').forEach((trip, index) => {
    const booking = window.bookingActionData?.[index];
    const packageDetails = trip.querySelector('.trip-package-detail');
    const bill = trip.querySelector('.trip-bill');
    const facts = trip.querySelector('.trip-facts');
    const total = bill?.querySelector('.trip-bill-total strong:last-child');

    if (facts && total) {
      const totalFact = document.createElement('span');
      const label = document.createElement('b');
      label.textContent = 'Total';
      totalFact.append(label, document.createTextNode(total.textContent.trim()));
      facts.append(totalFact);
    }

    if (booking) {
      trip.id = `booking-${booking.id}`;
      const totalLabel = bill?.querySelector('.trip-bill-total strong:first-child');
      if (totalLabel) totalLabel.textContent = booking.payment_status === 'paid' ? 'Total paid' : (booking.payment_status === 'refunded' ? 'Original total' : 'Total due');
      const adminNote = trip.querySelector('.admin-note');
      if (adminNote && booking.payment_status === 'paid' && /pay|payment|amount/i.test(adminNote.textContent)) {
        adminNote.innerHTML = '<b>Travel India update:</b> Payment received in full.';
      }
      const actions = document.createElement('div');
      actions.className = 'trip-actions';
      if (booking.status === 'needs_information') {
        const editLink = document.createElement('a');
        editLink.className = 'portal-button';
        editLink.href = `booking.php?edit_booking=${encodeURIComponent(booking.id)}`;
        editLink.innerHTML = '<i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Update booking request';
        actions.append(editLink);
      } else if (['paid', 'refund_pending', 'refunded'].includes(booking.payment_status)) {
        const invoiceLink = document.createElement('a');
        invoiceLink.className = 'portal-button';
        invoiceLink.href = `invoice.php?booking_id=${encodeURIComponent(booking.id)}`;
        invoiceLink.innerHTML = '<i class="fa-solid fa-file-invoice" aria-hidden="true"></i> View / download invoice';
        actions.append(invoiceLink);
      } else if (booking.status === 'confirmed' && ['unpaid', 'pending'].includes(booking.payment_status)) {
        const paymentLink = document.createElement('a');
        paymentLink.className = 'portal-button';
        paymentLink.href = `payment.php?booking_id=${encodeURIComponent(booking.id)}`;
        paymentLink.innerHTML = '<i class="fa-solid fa-credit-card" aria-hidden="true"></i> Continue to payment';
        actions.append(paymentLink);
      }
      const activeRequestStatuses = ['requested', 'under_review', 'customer_approval_required', 'refund_pending'];
      const isPaid = ['paid', 'refund_pending', 'refunded'].includes(booking.payment_status);
      const isBeforePayment = !isPaid;
      if (['confirmed', 'in_progress'].includes(booking.status) && booking.payment_status === 'paid' && !activeRequestStatuses.includes(booking.amendment_status) && !activeRequestStatuses.includes(booking.cancellation_status)) {
        const amendLink = document.createElement('a');
        amendLink.className = 'portal-button secondary';
        amendLink.href = `booking-request.php?type=amend&booking_id=${encodeURIComponent(booking.id)}`;
        amendLink.innerHTML = '<i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Request date change';
        actions.append(amendLink);
      }
      if (booking.payment_status === 'paid' && ['confirmed', 'in_progress'].includes(booking.status) && !activeRequestStatuses.includes(booking.cancellation_status) && !activeRequestStatuses.includes(booking.amendment_status)) {
        const cancelLink = document.createElement('a');
        cancelLink.className = 'portal-button danger-button';
        cancelLink.href = `booking-request.php?type=cancel&booking_id=${encodeURIComponent(booking.id)}`;
        cancelLink.innerHTML = '<i class="fa-solid fa-ban" aria-hidden="true"></i> Request cancellation';
        actions.append(cancelLink);
      }
      if (isBeforePayment && ['pending', 'needs_information', 'confirmed', 'rejected'].includes(booking.status)) {
        const deleteForm = document.createElement('form');
        deleteForm.method = 'post';
        deleteForm.action = 'booking-delete.php';
        deleteForm.onsubmit = () => confirm('Delete this unpaid booking? This removes the request and releases any reserved offer usage.');
        deleteForm.innerHTML = `<input type="hidden" name="csrf_token" value="${booking.csrf || ''}"><input type="hidden" name="booking_id" value="${booking.id}"><button class="portal-button danger-button" type="submit"><i class="fa-solid fa-trash" aria-hidden="true"></i> Delete booking</button>`;
        actions.append(deleteForm);
      }
      const activeRequest = activeRequestStatuses.includes(booking.cancellation_status) ? 'Cancellation under review' : (activeRequestStatuses.includes(booking.amendment_status) ? 'Date change under review' : '');
      if (activeRequest) {
        const status = document.createElement('span');
        status.className = 'trip-request-status';
        status.innerHTML = '<i class="fa-solid fa-clock" aria-hidden="true"></i> ' + activeRequest;
        actions.append(status);
      }
      if (actions.children.length && facts) facts.after(actions);
    }

    if (!packageDetails && !bill) return;

    const disclosure = document.createElement('details');
    disclosure.className = 'trip-expanded-details';
    const summary = document.createElement('summary');
    summary.textContent = packageDetails ? 'View package details and bill' : 'View price breakdown';
    disclosure.append(summary);
    (packageDetails || bill).before(disclosure);
    if (packageDetails) disclosure.append(packageDetails);
    if (bill) disclosure.append(bill);
  });
});
