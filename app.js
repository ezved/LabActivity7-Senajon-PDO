// Check that password confirmation matches the original password.
document.querySelectorAll('form[data-validate]').forEach((form) => {
  form.querySelectorAll('[data-match]').forEach((field) => {
    const source = form.querySelector(`#${CSS.escape(field.dataset.match)}`);
    if (!source) return;
    const match = () => field.setCustomValidity(field.value === source.value ? '' : 'The passwords do not match.');
    field.addEventListener('input', match);
    source.addEventListener('input', match);
  });
  // Update a character counter when a textarea has one beside it.
  form.querySelectorAll('textarea[maxlength]').forEach((field) => {
    const counter = field.closest('.field')?.querySelector('.help span:last-child');
    if (!counter) return;
    const update = () => { counter.textContent = `${field.value.length} / ${field.maxLength}`; };
    field.addEventListener('input', update);
    update();
  });
  // Ask the browser to display any built-in form validation errors.
  form.addEventListener('submit', (event) => {
    if (!form.reportValidity()) {
      event.preventDefault();
    }
  });
});

// Confirm before submitting a delete form.
document.querySelectorAll('[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  });
});
