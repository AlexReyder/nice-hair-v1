(function () {
  const ROOT_SELECTOR = "[data-nh-popup-salon]";
  const OPEN_CLASS = "is-open";
  const BODY_CLASS = "nh-popup-salon-open";
  const TRANSITION_MS = 300;
  const HASH = "#book";
  const DEFAULT_LABEL = "Book an appointment";
  const TRIGGER_SELECTORS = [
    "[data-popup-salon-trigger]",
    ".nh-salon-hero__actions .wp-block-button__link",
    ".nh-salon-our-approach__cta .wp-block-button__link",
    ".nh-salon-discounts__cta-link",
    ".nh-site-footer--salon .nh-site-footer__booking .wp-block-button__link",
  ];

  const root = document.querySelector(ROOT_SELECTOR);

  if (!(root instanceof HTMLElement)) {
    return;
  }

  const form = root.querySelector("[data-nh-popup-salon-form]");
  const success = root.querySelector("[data-nh-popup-salon-success]");
  const hint = root.querySelector("[data-nh-popup-salon-hint]");
  const successMessage = root.querySelector(
    "[data-nh-popup-salon-success-message]"
  );
  const whatsappLink = root.querySelector("[data-nh-popup-salon-whatsapp]");
  const submitButton = root.querySelector("[data-nh-popup-salon-submit]");
  const submitLabel =
    submitButton?.querySelector(".nh-popup-salon__submit-label") ?? submitButton;
  const closeButton = root.querySelector(".nh-popup-salon__close");

  if (!(form instanceof HTMLFormElement)) {
    return;
  }

  const getScrollLockState = () => {
    if (!window.__nhPopupScrollLockState) {
      window.__nhPopupScrollLockState = { count: 0 };
    }

    return window.__nhPopupScrollLockState;
  };
  const getScrollbarCompensation = () =>
    Math.max(0, window.innerWidth - document.documentElement.clientWidth);
  const acquireScrollLock = () => {
    if (state.scrollLocked) {
      return;
    }

    const scrollLockState = getScrollLockState();

    if (scrollLockState.count === 0) {
      document.documentElement.style.setProperty(
        "--nh-scrollbar-compensation",
        `${getScrollbarCompensation()}px`
      );
    }

    scrollLockState.count += 1;
    state.scrollLocked = true;
  };
  const releaseScrollLock = () => {
    if (!state.scrollLocked) {
      return;
    }

    const scrollLockState = getScrollLockState();

    scrollLockState.count = Math.max(0, scrollLockState.count - 1);
    state.scrollLocked = false;

    if (scrollLockState.count === 0) {
      document.documentElement.style.removeProperty(
        "--nh-scrollbar-compensation"
      );
    }
  };
  const state = {
    closeTimer: null,
    previousActiveElement: null,
    activeLabel: root.dataset.defaultLabel || DEFAULT_LABEL,
    activeTrigger: null,
    scrollLocked: false,
  };

  const isOpen = () => root.classList.contains(OPEN_CLASS);

  const setHash = () => {
    if (window.location.hash === HASH) {
      return;
    }

    const nextUrl = `${window.location.pathname}${window.location.search}${HASH}`;
    window.history.pushState({}, "", nextUrl);
  };

  const clearHash = () => {
    if (window.location.hash !== HASH) {
      return;
    }

    const nextUrl = `${window.location.pathname}${window.location.search}`;
    window.history.replaceState({}, "", nextUrl);
  };

  const setHint = (message = "") => {
    if (!(hint instanceof HTMLElement)) {
      return;
    }

    hint.textContent = message;
    hint.hidden = message === "";
  };

  const resetSuccess = () => {
    success?.setAttribute("hidden", "");
    form.removeAttribute("hidden");
    root.classList.remove("is-submitted");
  };

  const resetForm = () => {
    form.reset();
    const labelField = form.elements.namedItem("popup_label");
    const pageUrlField = form.elements.namedItem("page_url");

    if (labelField instanceof HTMLInputElement) {
      labelField.value = state.activeLabel || root.dataset.defaultLabel || DEFAULT_LABEL;
    }

    if (pageUrlField instanceof HTMLInputElement) {
      pageUrlField.value = window.location.href;
    }

    if (submitButton instanceof HTMLButtonElement) {
      submitButton.disabled = false;
    }

    if (submitLabel instanceof HTMLElement) {
      submitLabel.textContent = "SEND";
    }

    setHint("");
    resetSuccess();
  };

  const openPopup = (label = "", trigger = null) => {
    if (state.closeTimer) {
      window.clearTimeout(state.closeTimer);
      state.closeTimer = null;
    }

    state.previousActiveElement =
      document.activeElement instanceof HTMLElement ? document.activeElement : null;
    state.activeTrigger = trigger instanceof HTMLElement ? trigger : null;
    state.activeLabel = label || root.dataset.defaultLabel || DEFAULT_LABEL;

    resetForm();
    acquireScrollLock();
    root.hidden = false;
    document.body.classList.add(BODY_CLASS);
    void root.offsetWidth;
    root.classList.add(OPEN_CLASS);
    setHash();

    if (closeButton instanceof HTMLElement) {
      window.requestAnimationFrame(() => {
        closeButton.focus({ preventScroll: true });
      });
    }
  };

  const finishClose = () => {
    releaseScrollLock();
    root.hidden = true;
    document.body.classList.remove(BODY_CLASS);
  };

  const closePopup = ({ restoreFocus = true } = {}) => {
    if (state.closeTimer) {
      window.clearTimeout(state.closeTimer);
      state.closeTimer = null;
    }

    root.classList.remove(OPEN_CLASS);
    clearHash();

    state.closeTimer = window.setTimeout(() => {
      finishClose();
      state.closeTimer = null;
    }, TRANSITION_MS);

    if (restoreFocus) {
      const focusTarget =
        state.activeTrigger instanceof HTMLElement && state.activeTrigger.isConnected
          ? state.activeTrigger
          : state.previousActiveElement;

      if (focusTarget instanceof HTMLElement && focusTarget.isConnected) {
        focusTarget.focus({ preventScroll: true });
      }
    }

    state.activeTrigger = null;
    state.previousActiveElement = null;
  };

  const getTriggerLabel = (trigger) => {
    const explicitLabel = trigger.getAttribute("data-popup-label") || "";
    return explicitLabel.trim() || root.dataset.defaultLabel || DEFAULT_LABEL;
  };

  const isTrigger = (element) =>
    TRIGGER_SELECTORS.some((selector) => element.closest(selector));

  const openFromHash = () => {
    if (window.location.hash !== HASH || isOpen()) {
      return;
    }

    openPopup(root.dataset.defaultLabel || DEFAULT_LABEL, null);
  };

  const showSuccess = ({ message = "", whatsapp = "" }) => {
    root.classList.add("is-submitted");
    form.setAttribute("hidden", "");
    success?.removeAttribute("hidden");

    if (successMessage instanceof HTMLElement && message) {
      successMessage.innerHTML = String(message).replace(/\n/g, "<br>");
    }

    if (whatsappLink instanceof HTMLAnchorElement && whatsapp) {
      whatsappLink.href = `https://wa.me/${encodeURIComponent(whatsapp)}`;
      whatsappLink.hidden = false;
    } else if (whatsappLink instanceof HTMLAnchorElement) {
      whatsappLink.hidden = true;
    }
  };

  const validate = () => {
    const nameField = form.elements.namedItem("name");
    const phoneField = form.elements.namedItem("phone");

    const name =
      nameField instanceof HTMLInputElement ? nameField.value.trim() : "";
    const phoneRaw =
      phoneField instanceof HTMLInputElement ? phoneField.value.trim() : "";
    const phoneDigits = phoneRaw.replace(/\D+/g, "");

    if (!name) {
      return "Please enter your name.";
    }

    if (phoneDigits.length < 7 || phoneDigits.length > 15) {
      return "Please enter a valid phone number (7–15 digits).";
    }

    return "";
  };

  const getSubmitErrorMessage = (errorCode) => {
    switch (errorCode) {
      case "rate_limited":
        return "Too many attempts. Please try again a little later.";
      case "invalid_nonce":
      case "insert_failed":
        return "Something went wrong. Please try again.";
      default:
        return errorCode || "Something went wrong. Please try again.";
    }
  };

  const submit = async () => {
    const validationMessage = validate();

    if (validationMessage) {
      setHint(validationMessage);
      return;
    }

    const endpoint = root.dataset.endpoint || "";
    const nonce = root.dataset.nonce || "";

    if (!endpoint) {
      setHint("Submission endpoint is not configured.");
      return;
    }

    if (submitButton instanceof HTMLButtonElement) {
      submitButton.disabled = true;
    }

    if (submitLabel instanceof HTMLElement) {
      submitLabel.textContent = "SENDING…";
    }

    setHint("");

    try {
      const response = await fetch(endpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "X-WP-Nonce": nonce,
        },
        body: new FormData(form),
      });

      let json = null;

      try {
        json = await response.json();
      } catch (error) {
        json = null;
      }

      if (!response.ok || !json || json.success !== true) {
        throw new Error(
          (json && json.error) || `Request failed (${response.status})`
        );
      }

      showSuccess(json);
    } catch (error) {
      if (submitButton instanceof HTMLButtonElement) {
        submitButton.disabled = false;
      }

      if (submitLabel instanceof HTMLElement) {
        submitLabel.textContent = "SEND";
      }

      setHint(
        getSubmitErrorMessage(error && error.message)
      );
    }
  };

  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const trigger = event.target.closest(TRIGGER_SELECTORS.join(","));

    if (!(trigger instanceof HTMLElement) || !isTrigger(trigger)) {
      return;
    }

    event.preventDefault();
    openPopup(getTriggerLabel(trigger), trigger);
  });

  root.querySelectorAll("[data-nh-popup-salon-close]").forEach((closer) => {
    closer.addEventListener("click", () => closePopup());
  });

  form.addEventListener("input", () => setHint(""));

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    submit();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isOpen()) {
      closePopup();
    }
  });

  document.addEventListener("nice-hair:popup-salon-open", (event) => {
    const detail = event.detail || {};
    openPopup(detail.label || root.dataset.defaultLabel || DEFAULT_LABEL, null);
  });

  window.addEventListener("hashchange", () => {
    if (window.location.hash === HASH) {
      openFromHash();
      return;
    }

    if (isOpen()) {
      closePopup({ restoreFocus: false });
    }
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", openFromHash, { once: true });
  } else {
    openFromHash();
  }
})();
