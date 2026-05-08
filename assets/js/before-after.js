// Delegated click handler for [ BEFORE ] / [ AFTER ] toggle buttons.
//
// Cards may carry an optional data-before-after-sync-id attribute;
// when present, every card sharing that id is toggled together. That
// keeps Swiper loop clones and the original slide in sync — clicking
// AFTER on any copy stays visually consistent as the user swipes.
//
// Cards without a sync-id keep the original single-card behavior.
document.addEventListener('click', (event) => {
  const toggle = event.target.closest('[data-before-after-toggle]');

  if (!toggle) {
    return;
  }

  const card = toggle.closest('[data-before-after-card]');

  if (!card) {
    return;
  }

  const state = toggle.getAttribute('data-before-after-toggle') || 'before';
  const syncId = card.getAttribute('data-before-after-sync-id');

  if (syncId) {
    document
      .querySelectorAll(
        `[data-before-after-card][data-before-after-sync-id="${CSS.escape(syncId)}"]`
      )
      .forEach((el) => el.setAttribute('data-before-after-state', state));
  } else {
    card.setAttribute('data-before-after-state', state);
  }
});
