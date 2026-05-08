document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-faq-toggle]');
  if (!button) return;

  const item = button.closest('[data-faq-item]');
  if (!item) return;

  const isOpen = item.classList.contains('nh-faq__item--open');

  item.classList.toggle('nh-faq__item--open');
  button.setAttribute('aria-expanded', String(!isOpen));
});
