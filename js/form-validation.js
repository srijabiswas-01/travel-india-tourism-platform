(() => {
  const controls = 'input, select, textarea';

  const fieldLabel = (field) => {
    const label = field.labels?.[0] || field.closest('label');
    if (label) {
      const copy = label.cloneNode(true);
      copy.querySelectorAll('input, select, textarea, button, small, .field-error').forEach((item) => item.remove());
      const text = copy.textContent.trim().replace(/\s+/g, ' ').replace(/:$/, '');
      if (text) return text.toLowerCase();
    }
    return (field.name || field.id || 'this field').replace(/\[\]$/, '').replace(/[_-]+/g, ' ').toLowerCase();
  };

  const validationMessage = (field) => {
    const validity = field.validity;
    const label = fieldLabel(field);
    if (validity.customError) return field.validationMessage || 'Please check this value.';
    if (validity.valueMissing) {
      if (field.type === 'checkbox') return 'Please select this option to continue.';
      if (field.type === 'radio' || field.tagName === 'SELECT') return `Please choose ${label}.`;
      if (field.type === 'file') return `Please choose ${label}.`;
      return `Please enter ${label}.`;
    }
    if (validity.typeMismatch) {
      if (field.type === 'email') return 'Enter a valid email address, such as name@example.com.';
      if (field.type === 'url') return 'Enter a complete web address, including https://.';
      return `Enter a valid ${label}.`;
    }
    if (validity.patternMismatch) {
      if (field.title) return field.title;
      if (field.type === 'tel' || /phone|mobile/i.test(field.name)) return 'Enter a valid phone number.';
      return `Check the format for ${label}.`;
    }
    if (validity.tooShort) return `Use at least ${field.minLength} characters.`;
    if (validity.tooLong) return `Use no more than ${field.maxLength} characters.`;
    if (validity.rangeUnderflow) return field.type === 'date' ? `Choose a date on or after ${field.min}.` : `Enter a value of at least ${field.min}.`;
    if (validity.rangeOverflow) return field.type === 'date' ? `Choose a date on or before ${field.max}.` : `Enter a value no greater than ${field.max}.`;
    if (validity.stepMismatch) return `Enter a valid value for ${label}.`;
    if (validity.badInput) return `Enter a valid ${label}.`;
    return `Check ${label} and try again.`;
  };

  const errorNode = (field) => {
    let error = document.getElementById(field.dataset.validationErrorId || '');
    if (!error) {
      error = document.createElement('span');
      error.className = 'field-error';
      error.setAttribute('role', 'alert');
      error.id = `field-error-${Math.random().toString(36).slice(2, 10)}`;
      field.dataset.validationErrorId = error.id;
      const anchor = field.parentElement?.classList.contains('password-field') ? field.parentElement : field;
      anchor.insertAdjacentElement('afterend', error);
    }
    return error;
  };

  const showError = (field) => {
    if (!field.name || field.disabled || field.type === 'hidden') return;
    const error = errorNode(field);
    error.textContent = validationMessage(field);
    field.classList.add('field-invalid');
    field.setAttribute('aria-invalid', 'true');
    const descriptions = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
    descriptions.add(error.id);
    field.setAttribute('aria-describedby', Array.from(descriptions).join(' '));
  };

  const clearError = (field) => {
    field.classList.remove('field-invalid');
    field.removeAttribute('aria-invalid');
    const errorId = field.dataset.validationErrorId;
    if (!errorId) return;
    const descriptions = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter((id) => id && id !== errorId);
    if (descriptions.length) field.setAttribute('aria-describedby', descriptions.join(' '));
    else field.removeAttribute('aria-describedby');
    document.getElementById(errorId)?.remove();
    delete field.dataset.validationErrorId;
  };

  const syncRelatedRules = (form) => {
    const password = form.querySelector('[name="new_password"]') || form.querySelector('[name="password"]');
    form.querySelectorAll('[name="confirm_password"], [name="password_confirmation"]').forEach((confirmation) => {
      if (!password || password === confirmation) return;
      confirmation.setCustomValidity(confirmation.value && confirmation.value !== password.value ? 'Passwords do not match. Re-enter the same password.' : '');
    });

    const startDate = form.querySelector('[name="from_date"]');
    const endDate = form.querySelector('[name="to_date"]');
    if (startDate && endDate) endDate.setCustomValidity(startDate.value && endDate.value && endDate.value < startDate.value ? 'Choose an end date that is the same as or later than the start date.' : '');

    const status = form.querySelector('[name="status"]');
    const adminNote = form.querySelector('[name="admin_note"]');
    if (status && adminNote && ['needs_information', 'cancelled', 'rejected'].includes(status.value)) adminNote.setCustomValidity(adminNote.value.trim() ? '' : 'Add a customer update for this status.');
    else if (adminNote) adminNote.setCustomValidity('');
  };

  const initialize = () => {
    document.querySelectorAll('form').forEach((form) => syncRelatedRules(form));

    document.addEventListener('invalid', (event) => {
      const field = event.target;
      if (!(field instanceof HTMLElement) || !field.matches(controls)) return;
      const form = field.closest('form');
      if (form) {
        syncRelatedRules(form);
        form.querySelectorAll('[data-validation-error-id]').forEach((related) => {
          if (related.validity.valid) clearError(related);
          else showError(related);
        });
      }
      if (!field.validity.valid) {
        event.preventDefault();
        showError(field);
      }
    }, true);

    const updateField = (event) => {
      const field = event.target;
      if (!(field instanceof HTMLElement) || !field.matches(controls)) return;
      const form = field.closest('form');
      if (form) syncRelatedRules(form);
      if (field.dataset.validationErrorId && field.validity.valid) clearError(field);
      else if (field.dataset.validationErrorId && !field.validity.valid) showError(field);
    };

    document.addEventListener('input', updateField);
    document.addEventListener('change', updateField);
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
  else initialize();
})();