document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#payment-form');
  if (!form) return;

  const methods = [...form.querySelectorAll('input[name="method"]')];
  const panels = [...form.querySelectorAll('[data-method-panel]')];
  const cardNumber = form.querySelector('#practice-card-number');
  const cardExpiry = form.querySelector('#practice-card-expiry');
  const upiId = form.querySelector('#practice-upi-id');
  const currentMonth = new Date().toISOString().slice(0, 7);

  if (cardExpiry) cardExpiry.min = currentMonth;

  const luhnValid = (value) => {
    const digits = value.replace(/\D/g, '');
    if (digits.length < 13 || digits.length > 19) return false;
    let sum = 0;
    let doubleDigit = false;
    for (let index = digits.length - 1; index >= 0; index -= 1) {
      let digit = Number(digits[index]);
      if (doubleDigit) {
        digit *= 2;
        if (digit > 9) digit -= 9;
      }
      sum += digit;
      doubleDigit = !doubleDigit;
    }
    return sum % 10 === 0;
  };

  const setPanel = () => {
    const selectedMethod = form.querySelector('input[name="method"]:checked')?.value;
    panels.forEach((panel) => {
      const active = panel.dataset.methodPanel === selectedMethod;
      panel.hidden = !active;
      panel.querySelectorAll('input, select').forEach((field) => { field.disabled = !active; });
    });
  };

  const messageFor = (field) => {
    if (field.validity.valueMissing) return 'Please complete this field.';
    if (field === upiId && field.validity.patternMismatch) return 'Enter a UPI ID in the format name@bank.';
    if (field === cardNumber && !luhnValid(field.value)) return 'Enter a valid card number.';
    if (field === cardExpiry && field.validity.rangeUnderflow) return 'Choose a current or future expiry month.';
    if (field === form.querySelector('#practice-card-cvv') && field.validity.patternMismatch) return 'Enter the 3- or 4-digit security code.';
    if (field.validity.typeMismatch) return 'Enter a valid value.';
    if (field.validity.patternMismatch) return 'Check the field format and try again.';
    return field.validationMessage || 'Check this field and try again.';
  };

  const showError = (field) => {
    let error = field.parentElement.querySelector('.checkout-inline-error');
    if (!error) {
      error = document.createElement('small');
      error.className = 'checkout-inline-error';
      error.setAttribute('role', 'alert');
      field.insertAdjacentElement('afterend', error);
    }
    error.textContent = messageFor(field);
    field.setAttribute('aria-invalid', 'true');
  };

  const clearError = (field) => {
    field.parentElement.querySelector('.checkout-inline-error')?.remove();
    field.removeAttribute('aria-invalid');
  };

  if (cardNumber) {
    cardNumber.addEventListener('input', () => {
      const digits = cardNumber.value.replace(/\D/g, '').slice(0, 19);
      cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
      cardNumber.setCustomValidity(cardNumber.value && !luhnValid(cardNumber.value) ? 'Enter a valid card number.' : '');
    });
  }

  form.querySelectorAll('[data-checkout-field]').forEach((field) => {
    field.addEventListener('input', () => {
      if (field.validity.valid && (field !== cardNumber || luhnValid(field.value))) clearError(field);
      else if (field.getAttribute('aria-invalid') === 'true') showError(field);
    });
    field.addEventListener('change', () => {
      if (field.validity.valid && (field !== cardNumber || luhnValid(field.value))) clearError(field);
      else if (field.getAttribute('aria-invalid') === 'true') showError(field);
    });
  });

  document.addEventListener('invalid', (event) => {
    const field = event.target;
    if (!(field instanceof HTMLElement) || !field.matches('[data-checkout-field]')) return;
    event.preventDefault();
    showError(field);
  }, true);

  methods.forEach((method) => method.addEventListener('change', setPanel));
  setPanel();

  const qrElement = document.querySelector('#practice-qr');
  if (qrElement) {
    const qrPayload = qrElement.dataset.qrPayload || 'TRAVEL-INDIA-REFERENCE';
    if (window.QRCode) {
      new QRCode(qrElement, { text: qrPayload, width: 148, height: 148, correctLevel: QRCode.CorrectLevel.M });
    } else {
      qrElement.textContent = qrPayload;
      qrElement.classList.add('checkout-qr-fallback');
    }
  }

  form.addEventListener('submit', (event) => {
    const activePanel = panels.find((panel) => !panel.hidden);
    const invalidField = activePanel?.querySelector('input:invalid, select:invalid');
    const activeCardNumber = activePanel?.querySelector('#practice-card-number');
    if (activeCardNumber && !luhnValid(activeCardNumber.value)) {
      activeCardNumber.setCustomValidity('Enter a valid card number.');
      showError(activeCardNumber);
      event.preventDefault();
      activeCardNumber.focus();
      return;
    }
    if (invalidField) {
      showError(invalidField);
      event.preventDefault();
      invalidField.focus();
      return;
    }

    form.querySelectorAll('[data-sensitive]').forEach((field) => {
      field.value = '';
      field.disabled = true;
    });
  });
});
