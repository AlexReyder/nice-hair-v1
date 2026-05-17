(function () {
  const ROOT_SELECTOR = `[data-nh-cookie-consent]`;
  const SETTINGS_TRIGGER_SELECTOR = `[data-nh-cookie-settings-trigger]`;
  const SETTINGS_OPEN_SELECTOR = `[data-nh-cookie-settings-open]`;
  const SETTINGS_CLOSE_SELECTOR = `[data-nh-cookie-settings-close]`;
  const ACTION_SELECTOR = `[data-nh-cookie-action]`;
  const CATEGORY_SELECTOR = `[data-nh-cookie-category]`;
  const OPEN_CLASS = `is-open`;
  const SETTINGS_OPEN_CLASS = `is-settings-open`;
  const BODY_CLASS = `nh-cookie-consent-open`;
  const COOKIE_NAME = `nh_cookie_consent`;
  const TRANSITION_MS = 260;

  const root = document.querySelector(ROOT_SELECTOR);

  if (!(root instanceof HTMLElement)) {
    return;
  }

  const mainPanel = root.querySelector(`[data-nh-cookie-main-panel]`);
  const settingsPanel = root.querySelector(`[data-nh-cookie-settings-panel]`);
  const closeButton = root.querySelector(`[data-nh-cookie-action="close"]`);
  const version = Number.parseInt(root.dataset.consentVersion || `1`, 10) || 1;
  const expirationDays = Number.parseInt(root.dataset.consentExpirationDays || `180`, 10) || 180;
  let closeTimer = null;
  let previousActiveElement = null;

  const getCategoryInput = (category) =>
    root.querySelector(`[data-nh-cookie-category="${category}"]`);

  const hasOptionalCategory = (category) =>
    getCategoryInput(category) instanceof HTMLInputElement;

  const readCookie = (name) => {
    const cookies = document.cookie ? document.cookie.split(`;`).map((item) => item.trim()) : [];
    const prefix = `${name}=`;
    const cookie = cookies.find((item) => item.startsWith(prefix));

    if (!cookie) {
      return ``;
    }

    return cookie.slice(prefix.length);
  };

  const parseConsent = () => {
    const rawConsent = readCookie(COOKIE_NAME);

    if (!rawConsent) {
      return null;
    }

    try {
      const parsed = JSON.parse(decodeURIComponent(rawConsent));

      return parsed && typeof parsed === `object` ? parsed : null;
    } catch (error) {
      return null;
    }
  };

  const isConsentValid = (consent) =>
    Boolean(
      consent &&
        consent.version === version &&
        consent.necessary === true
    );

  const getConsent = () => {
    const consent = parseConsent();

    return isConsentValid(consent) ? consent : null;
  };

  const hasConsent = (category) => {
    const consent = getConsent();

    if (!consent) {
      return false;
    }

    if (category === `necessary`) {
      return true;
    }

    return consent[category] === true;
  };

  const getCookieAttributes = () => {
    const maxAge = Math.max(1, expirationDays) * 24 * 60 * 60;
    const attributes = [`path=/`, `max-age=${maxAge}`, `SameSite=Lax`];

    if (window.location.protocol === `https:`) {
      attributes.push(`Secure`);
    }

    return attributes.join(`; `);
  };

  const setConsent = (consent) => {
    const normalizedConsent = {
      version,
      necessary: true,
      analytics: consent.analytics === true,
      marketing: consent.marketing === true,
      updatedAt: new Date().toISOString(),
    };

    document.cookie = `${COOKIE_NAME}=${encodeURIComponent(
      JSON.stringify(normalizedConsent)
    )}; ${getCookieAttributes()}`;

    document.dispatchEvent(
      new CustomEvent(`nh:cookie-consent-updated`, {
        detail: { consent: normalizedConsent },
      })
    );

    document.dispatchEvent(
      new CustomEvent(`nice-hair:cookie-consent-updated`, {
        detail: { consent: normalizedConsent },
      })
    );

    syncCloseButton();
    hideBanner();
  };

  const syncCloseButton = () => {
    if (!(closeButton instanceof HTMLElement)) {
      return;
    }

    closeButton.hidden = !isConsentValid(parseConsent());
  };

  const syncCategoryInputs = () => {
    const consent = getConsent();

    root.querySelectorAll(CATEGORY_SELECTOR).forEach((input) => {
      if (!(input instanceof HTMLInputElement)) {
        return;
      }

      const category = input.dataset.nhCookieCategory || ``;

      if (category === `necessary`) {
        input.checked = true;
        return;
      }

      input.checked = consent ? consent[category] === true : false;
    });
  };

  const focusFirstInteractiveElement = (container) => {
    if (!(container instanceof HTMLElement)) {
      return;
    }

    const focusable = container.querySelector(
      `button:not([disabled]):not([hidden]), input:not([disabled]):not([hidden]), a[href]`
    );

    if (focusable instanceof HTMLElement) {
      window.requestAnimationFrame(() => {
        focusable.focus({ preventScroll: true });
      });
    }
  };

  const openSettingsPanel = () => {
    if (!(settingsPanel instanceof HTMLElement)) {
      return;
    }

    syncCategoryInputs();
    root.classList.add(SETTINGS_OPEN_CLASS);
    settingsPanel.hidden = false;

    if (mainPanel instanceof HTMLElement) {
      mainPanel.hidden = true;
    }

    focusFirstInteractiveElement(settingsPanel);
  };

  const closeSettingsPanel = () => {
    if (!(settingsPanel instanceof HTMLElement)) {
      return;
    }

    root.classList.remove(SETTINGS_OPEN_CLASS);
    settingsPanel.hidden = true;

    if (mainPanel instanceof HTMLElement) {
      mainPanel.hidden = false;
      focusFirstInteractiveElement(mainPanel);
    }
  };

  const showBanner = (options = {}) => {
    const { openSettings = false } = options;

    if (closeTimer) {
      window.clearTimeout(closeTimer);
      closeTimer = null;
    }

    previousActiveElement =
      document.activeElement instanceof HTMLElement ? document.activeElement : null;

    syncCloseButton();
    root.hidden = false;

    if (openSettings) {
      openSettingsPanel();
    } else {
      closeSettingsPanel();
    }

    void root.offsetWidth;
    root.classList.add(OPEN_CLASS);
    document.body.classList.add(BODY_CLASS);

    if (!openSettings && mainPanel instanceof HTMLElement) {
      focusFirstInteractiveElement(mainPanel);
    }
  };

  const hideBanner = () => {
    if (closeTimer) {
      window.clearTimeout(closeTimer);
      closeTimer = null;
    }

    root.classList.remove(OPEN_CLASS);
    root.classList.remove(SETTINGS_OPEN_CLASS);
    document.body.classList.remove(BODY_CLASS);

    closeTimer = window.setTimeout(() => {
      root.hidden = true;

      if (settingsPanel instanceof HTMLElement) {
        settingsPanel.hidden = true;
      }

      if (mainPanel instanceof HTMLElement) {
        mainPanel.hidden = false;
      }

      closeTimer = null;
    }, TRANSITION_MS);

    if (
      previousActiveElement instanceof HTMLElement &&
      previousActiveElement.isConnected
    ) {
      previousActiveElement.focus({ preventScroll: true });
    }

    previousActiveElement = null;
  };

  const acceptAll = () => {
    setConsent({
      analytics: hasOptionalCategory(`analytics`),
      marketing: hasOptionalCategory(`marketing`),
    });
  };

  const rejectOptional = () => {
    setConsent({
      analytics: false,
      marketing: false,
    });
  };

  const saveChoices = () => {
    const analyticsInput = getCategoryInput(`analytics`);
    const marketingInput = getCategoryInput(`marketing`);

    setConsent({
      analytics:
        analyticsInput instanceof HTMLInputElement ? analyticsInput.checked : false,
      marketing:
        marketingInput instanceof HTMLInputElement ? marketingInput.checked : false,
    });
  };

  root.addEventListener(`click`, (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const settingsOpenTrigger = event.target.closest(SETTINGS_OPEN_SELECTOR);

    if (settingsOpenTrigger) {
      event.preventDefault();
      openSettingsPanel();
      return;
    }

    const settingsCloseTrigger = event.target.closest(SETTINGS_CLOSE_SELECTOR);

    if (settingsCloseTrigger) {
      event.preventDefault();
      closeSettingsPanel();
      return;
    }

    const actionTrigger = event.target.closest(ACTION_SELECTOR);

    if (!actionTrigger) {
      return;
    }

    event.preventDefault();

    const action = actionTrigger.getAttribute(`data-nh-cookie-action`) || ``;

    if (action === `accept-all`) {
      acceptAll();
      return;
    }

    if (action === `reject-optional`) {
      rejectOptional();
      return;
    }

    if (action === `save-choices`) {
      saveChoices();
      return;
    }

    if (action === `close` && isConsentValid(parseConsent())) {
      hideBanner();
    }
  });

  document.addEventListener(`click`, (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const trigger = event.target.closest(SETTINGS_TRIGGER_SELECTOR);

    if (!trigger) {
      return;
    }

    event.preventDefault();
    showBanner({ openSettings: true });
  });

  document.addEventListener(`keydown`, (event) => {
    if (event.key !== `Escape`) {
      return;
    }

    if (!root.classList.contains(OPEN_CLASS)) {
      return;
    }

    if (root.classList.contains(SETTINGS_OPEN_CLASS)) {
      closeSettingsPanel();
      return;
    }

    if (isConsentValid(parseConsent())) {
      hideBanner();
    }
  });

  window.nhCookieConsent = {
    getConsent,
    hasConsent,
    openSettings: () => showBanner({ openSettings: true }),
  };

  syncCloseButton();

  if (!isConsentValid(parseConsent())) {
    showBanner();
  }
})();
