document.addEventListener('click', function (event) {
  const button = event.target.closest('[data-password-toggle]');
  if (!button) return;
  const field = document.getElementById(button.dataset.passwordToggle);
  if (!field) return;
  const showing = field.type === 'text';
  field.type = showing ? 'password' : 'text';
  button.setAttribute('aria-pressed', String(!showing));
  button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
  button.setAttribute('title', showing ? 'Show password' : 'Hide password');
  const icon = button.querySelector('i');
  if (icon) { icon.classList.toggle('fa-eye', showing); icon.classList.toggle('fa-eye-slash', !showing); }
});
