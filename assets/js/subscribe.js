/**
 * Shared newsletter subscribe form handler.
 */
(function () {
  const forms = document.querySelectorAll('[data-subscribe-form]');
  if (!forms.length) return;

  forms.forEach((form) => {
    const status = form.querySelector('[data-subscribe-status]');
    const input = form.querySelector('input[name="email"]');
    const endpoint =
      window.nhSubscribe?.restUrl || '/hair/wp-json/nice-hair/v1/subscribe';
    const successClass = 'nh-subscribe__status--success';
    const errorClass = 'nh-subscribe__status--error';

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const email = input?.value?.trim();
      if (!email) return;

      form.classList.add('nh-subscribe__form--loading');
      if (status) {
        status.textContent = '';
        status.classList.remove(successClass, errorClass);
        status.classList.add('nh-subscribe__status');
      }

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.nhSubscribe?.nonce || '',
          },
          body: JSON.stringify({
            email,
            hp_field: form.querySelector('input[name="hp_field"]')?.value || '',
            source: form.querySelector('input[name="source"]')?.value || '',
          }),
        });

        const data = await res.json();

        if (status) {
          if (data.success) {
            status.textContent = data.message || 'Subscribed!';
            status.classList.remove(errorClass);
            status.classList.add('nh-subscribe__status', successClass);
            if (input) input.value = '';
          } else {
            const msg =
              data.error === 'invalid_email'
                ? 'Please enter a valid email address.'
                : data.error === 'rate_limited'
                  ? 'Too many attempts. Please try again later.'
                  : 'Something went wrong. Please try again.';
            status.textContent = msg;
            status.classList.remove(successClass);
            status.classList.add('nh-subscribe__status', errorClass);
          }
        }
      } catch {
        if (status) {
          status.textContent = 'Network error. Please try again.';
          status.classList.remove(successClass);
          status.classList.add('nh-subscribe__status', errorClass);
        }
      } finally {
        form.classList.remove('nh-subscribe__form--loading');
      }
    });
  });
})();
