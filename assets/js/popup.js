(function () {
  const POPUP_SELECTOR = "[data-nh-popup]";
  const OPEN_CLASS = "is-open";
  const BODY_CLASS = "nh-site-popup-open";
  const TRANSITION_MS = 350;
  const DAY_IN_MS = 24 * 60 * 60 * 1000;
  const popups = Array.from(document.querySelectorAll(POPUP_SELECTOR));

  if (!popups.length) {
    return;
  }

  const stateMap = new WeakMap();
  const getScrollLockState = () => {
    if (!window.__nhPopupScrollLockState) {
      window.__nhPopupScrollLockState = { count: 0 };
    }

    return window.__nhPopupScrollLockState;
  };
  const getScrollbarCompensation = () =>
    Math.max(0, window.innerWidth - document.documentElement.clientWidth);
  const acquireScrollLock = (state) => {
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
  const releaseScrollLock = (state) => {
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

  const safeStorage = (storage) => ({
    get(key) {
      try {
        return storage.getItem(key);
      } catch (error) {
        return null;
      }
    },
    set(key, value) {
      try {
        storage.setItem(key, value);
      } catch (error) {
        return;
      }
    },
  });

  const storageAdapters = {
    local: safeStorage(window.localStorage),
    session: safeStorage(window.sessionStorage),
  };

  const getState = (popup) => {
    if (!stateMap.has(popup)) {
      stateMap.set(popup, {
        closeTimer: null,
        openTimer: null,
        previousActiveElement: null,
        scrollLocked: false,
      });
    }

    return stateMap.get(popup);
  };

  const getDisplayMode = (popup) => {
    const displayMode = popup.dataset.displayMode || "";

    if (displayMode === "session" || displayMode === "days") {
      return displayMode;
    }

    return "once";
  };

  const getRepeatDays = (popup) => {
    const value = Number.parseInt(popup.dataset.repeatDays || "0", 10);
    return Math.max(1, value || 1);
  };

  const isSeen = (popup) => {
    const storageKey = popup.dataset.storageKey;

    if (!storageKey) {
      return false;
    }

    switch (getDisplayMode(popup)) {
      case "session":
        return storageAdapters.session.get(storageKey) === "1";
      case "days": {
        const rawValue = storageAdapters.local.get(storageKey);
        const openedAt = Number.parseInt(rawValue || "", 10);

        if (!Number.isFinite(openedAt)) {
          return false;
        }

        return Date.now() - openedAt < getRepeatDays(popup) * DAY_IN_MS;
      }
      case "once":
      default:
        return storageAdapters.local.get(storageKey) === "1";
    }
  };

  const markSeen = (popup) => {
    const storageKey = popup.dataset.storageKey;

    if (!storageKey) {
      return;
    }

    switch (getDisplayMode(popup)) {
      case "session":
        storageAdapters.session.set(storageKey, "1");
        break;
      case "days":
        storageAdapters.local.set(storageKey, String(Date.now()));
        break;
      case "once":
      default:
        storageAdapters.local.set(storageKey, "1");
        break;
    }
  };

  const syncBodyState = () => {
    if (!document.querySelector(`${POPUP_SELECTOR}.${OPEN_CLASS}`)) {
      document.body.classList.remove(BODY_CLASS);
    }
  };

  const hidePopup = (popup) => {
    releaseScrollLock(getState(popup));
    popup.hidden = true;
    syncBodyState();
  };

  const closePopup = (popup, options = {}) => {
    const { restoreFocus = true } = options;
    const state = getState(popup);

    if (state.openTimer) {
      window.clearTimeout(state.openTimer);
      state.openTimer = null;
    }

    if (state.closeTimer) {
      window.clearTimeout(state.closeTimer);
      state.closeTimer = null;
    }

    popup.classList.remove(OPEN_CLASS);

    state.closeTimer = window.setTimeout(() => {
      hidePopup(popup);
      state.closeTimer = null;
    }, TRANSITION_MS);

    if (
      restoreFocus &&
      state.previousActiveElement instanceof HTMLElement &&
      state.previousActiveElement.isConnected
    ) {
      state.previousActiveElement.focus({ preventScroll: true });
    }

    state.previousActiveElement = null;
  };

  const openPopup = (popup, options = {}) => {
    const { manual = false } = options;
    const state = getState(popup);

    if (!manual && isSeen(popup)) {
      return;
    }

    popups.forEach((item) => {
      if (item !== popup && item.classList.contains(OPEN_CLASS)) {
        closePopup(item, { restoreFocus: false });
      }
    });

    if (state.openTimer) {
      window.clearTimeout(state.openTimer);
      state.openTimer = null;
    }

    if (state.closeTimer) {
      window.clearTimeout(state.closeTimer);
      state.closeTimer = null;
    }

    markSeen(popup);

    state.previousActiveElement =
      document.activeElement instanceof HTMLElement ? document.activeElement : null;

    acquireScrollLock(state);
    popup.hidden = false;
    document.body.classList.add(BODY_CLASS);
    void popup.offsetWidth;
    popup.classList.add(OPEN_CLASS);

    const closeButton = popup.querySelector("[data-nh-popup-close-button]");

    if (closeButton instanceof HTMLElement) {
      window.requestAnimationFrame(() => {
        closeButton.focus({ preventScroll: true });
      });
    }
  };

  const scheduleAutoOpen = (popup) => {
    if (isSeen(popup)) {
      return;
    }

    const delayMs = Number.parseInt(popup.dataset.delayMs || "0", 10);
    const state = getState(popup);

    state.openTimer = window.setTimeout(() => {
      openPopup(popup);
      state.openTimer = null;
    }, Math.max(0, delayMs));
  };

  popups.forEach((popup) => {
    popup.querySelectorAll("[data-nh-popup-close]").forEach((closer) => {
      closer.addEventListener("click", () => closePopup(popup));
    });

    scheduleAutoOpen(popup);
  });

  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const trigger = event.target.closest("[data-popup-target]");

    if (!trigger) {
      return;
    }

    event.preventDefault();
    const popupId = trigger.getAttribute("data-popup-target");

    document.dispatchEvent(
      new CustomEvent("nice-hair:popup-open", { detail: { popupId } })
    );
  });

  document.addEventListener("nice-hair:popup-open", (event) => {
    const popupId = event.detail?.popupId || "";
    const popup =
      popups.find((item) => !popupId || item.dataset.popupId === popupId) || null;

    if (popup) {
      openPopup(popup, { manual: true });
    }
  });

  document.addEventListener("nice-hair:popup-close", (event) => {
    const popupId = event.detail?.popupId || "";
    const popup =
      popups.find((item) => !popupId || item.dataset.popupId === popupId) || null;

    if (popup) {
      closePopup(popup);
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") {
      return;
    }

    const openedPopup = popups.find((popup) => popup.classList.contains(OPEN_CLASS));

    if (openedPopup) {
      closePopup(openedPopup);
    }
  });
})();
