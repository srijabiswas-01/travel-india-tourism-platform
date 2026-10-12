document.addEventListener('DOMContentLoaded', () => {
  const workflowForms = [...document.querySelectorAll('.inline-workflow')];
  if (!workflowForms.length) return;
  const pageHead = document.querySelector('.page-head');
  if (pageHead) {
    const params = new URLSearchParams(location.search);
    const filter = document.createElement('form');
    filter.className = 'booking-filter-bar';
    filter.method = 'get';
    filter.innerHTML = `<input name="q" value="${(params.get('q') || '').replaceAll('&', '&amp;').replaceAll('"', '&quot;')}" placeholder="Reference, customer, email, package, or destination"><select name="status"><option value="">All booking statuses</option>${['pending','needs_information','confirmed','in_progress','completed','cancelled','rejected'].map(v=>`<option value="${v}"${params.get('status')===v?' selected':''}>${v.replaceAll('_',' ')}</option>`).join('')}</select><select name="payment"><option value="">All payment statuses</option>${['unpaid','pending','paid','refund_pending','refunded'].map(v=>`<option value="${v}"${params.get('payment')===v?' selected':''}>${v.replaceAll('_',' ')}</option>`).join('')}</select><select name="request"><option value="">All customer requests</option><option value="amendment"${params.get('request')==='amendment'?' selected':''}>Date change waiting</option><option value="cancellation"${params.get('request')==='cancellation'?' selected':''}>Cancellation/refund waiting</option></select><button class="primary-btn"><i class="fa-solid fa-magnifying-glass"></i> Filter</button><a class="reset-btn" href="bookings.php">Reset</a>`;
    pageHead.insertAdjacentElement('afterend', filter);
  }

  let workflowData = {};
  let today = '';
  fetch('booking-workflow-data.php', { headers: { Accept: 'application/json' } })
    .then((response) => response.ok ? response.json() : Promise.reject())
    .then((payload) => {
      workflowData = payload.bookings || {};
      today = payload.today || '';
      workflowForms.forEach((form) => enhanceRequestState(form));
    })
    .catch(() => {});

  const enhanceRequestState = (form) => {
    const booking = workflowData[form.querySelector('[name="id"]')?.value];
    const row = form.closest('tr');
    if (!booking || !row) return;
    const statusSelect = form.querySelector('[name="status"]');
    if (statusSelect && booking.status === 'confirmed' && (booking.payment_status !== 'paid' || today < booking.from_date)) {
      const option=statusSelect.querySelector('[value="in_progress"]');if(option){option.disabled=true;option.title=booking.payment_status!=='paid'?'Full payment is required':'Available on the trip start date';}
    }
    if (statusSelect && booking.status === 'in_progress' && (booking.payment_status !== 'paid' || today < booking.to_date)) {
      const option=statusSelect.querySelector('[value="completed"]');if(option){option.disabled=true;option.title=booking.payment_status!=='paid'?'Full payment is required':'Available after the trip end date';}
      const help=document.createElement('small');help.className='workflow-help';help.textContent=booking.payment_status!=='paid'?'Completion requires full payment.':`Completion becomes available after ${booking.to_date}.`;form.append(help);
    }
    const active = ['requested','under_review','refund_pending'];
    const request = active.includes(booking.cancellation_status) ? `Cancellation: ${booking.cancellation_status.replaceAll('_',' ')}` : (active.includes(booking.amendment_status) ? `Date change: ${booking.amendment_status.replaceAll('_',' ')}` : '');
    if (request) {
      const badge = document.createElement('a');
      badge.className = 'request-alert';
      badge.href = `booking-details.php?id=${encodeURIComponent(booking.id)}`;
      badge.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${request}`;
      form.insertAdjacentElement('beforebegin', badge);
    }
    if (booking.status === 'confirmed' && booking.payment_status === 'paid' && today >= booking.from_date) row.querySelector('td:nth-child(3)')?.insertAdjacentHTML('beforeend','<em class="workflow-warning">Ready to start</em>');
    if (booking.status === 'in_progress' && today >= booking.to_date) row.querySelector('td:nth-child(3)')?.insertAdjacentHTML('beforeend','<em class="workflow-warning">Ready to complete</em>');
  };

  workflowForms.forEach((form) => {
    const bookingId = form.querySelector('[name="id"]')?.value;
    const noteInput = form.querySelector('input[name="admin_note"]');
    const row = form.closest('tr');
    const reference = row?.querySelector('td:first-child strong');
    const statusSelect = form.querySelector('[name="status"]');
    const paymentSelect = form.querySelector('[name="payment_status"]');

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

    if (statusSelect && paymentSelect) {
      const currentStatus = statusSelect.value;
      const currentPayment = paymentSelect.value;
      const terminal = ['completed', 'cancelled', 'rejected'].includes(currentStatus);
      const transitions = {
        pending: ['pending', 'needs_information', 'confirmed', 'cancelled', 'rejected'],
        needs_information: ['needs_information', 'confirmed', 'cancelled', 'rejected'],
        confirmed: ['confirmed', 'in_progress', 'cancelled'],
        in_progress: ['in_progress', 'completed', 'cancelled'],
        completed: ['completed'], cancelled: ['cancelled'], rejected: ['rejected']
      };
      [...statusSelect.options].forEach((option) => {
        if (!transitions[currentStatus]?.includes(option.value)) { option.disabled=true;option.title='Not available from the current booking status'; }
      });
      const allowedPayments = {
        unpaid: ['unpaid', 'pending', 'paid'], pending: ['pending', 'unpaid', 'paid'],
        paid: ['paid'], refund_pending: ['refund_pending', 'refunded'], refunded: ['refunded']
      };
      [...paymentSelect.options].forEach((option) => {
        if (!allowedPayments[currentPayment]?.includes(option.value)) { option.disabled=true;option.title='Not available from the current payment status'; }
      });
      if (terminal && currentPayment !== 'refund_pending') {
        form.querySelectorAll('select, textarea, button').forEach((control) => { control.disabled = true; });
        form.classList.add('is-read-only');
      }
      const review = document.createElement('label');
      review.className = 'workflow-review';
      review.innerHTML = '<input type="checkbox" name="availability_reviewed" value="1"> Reviewed';
      statusSelect.insertAdjacentElement('afterend', review);
      const syncReview = () => { review.hidden = statusSelect.value !== 'confirmed' || currentStatus === 'confirmed'; };
      statusSelect.addEventListener('change', syncReview);
      syncReview();
      if (currentPayment === 'paid') {
        [...statusSelect.options].forEach((option) => {
          if (['cancelled', 'rejected'].includes(option.value)) { option.disabled=true;option.title=option.value==='cancelled'?'Review the cancellation request from booking details':'A paid booking cannot be rejected'; }
        });
      }
    }
  });
});
