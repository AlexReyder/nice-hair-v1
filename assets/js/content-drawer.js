(function () {
  const DRAWER_SELECTOR = `[data-nh-content-drawer]`;
  const DRAWER_TRIGGER_SELECTOR = `[data-nh-content-drawer-target]`;
  const DRAWER_CLOSE_SELECTOR = `[data-nh-content-drawer-close]`;
  const OPEN_CLASS = `is-open`;
  const BODY_CLASS = `nh-content-drawer-open`;
  const TRANSITION_MS = 350;

  const drawers = Array.from(document.querySelectorAll(DRAWER_SELECTOR));

  if (!drawers.length) {
    return;
  }

  const stateMap = new WeakMap();
  const getScrollbarCompensation = () =>
    Math.max(0, window.innerWidth - document.documentElement.clientWidth);

  const getState = (drawer) => {
    if (!stateMap.has(drawer)) {
      stateMap.set(drawer, {
        closeTimer: null,
        previousActiveElement: null,
      });
    }

    return stateMap.get(drawer);
  };

  const getDrawerId = (drawer) => drawer.dataset.nhContentDrawerId || drawer.id || ``;

  const syncBodyState = () => {
    if (!document.querySelector(`${DRAWER_SELECTOR}.${OPEN_CLASS}`)) {
      document.body.classList.remove(BODY_CLASS);
      document.documentElement.style.removeProperty(`--nh-scrollbar-compensation`);
    }
  };

  const hideDrawer = (drawer) => {
    drawer.hidden = true;
    syncBodyState();
  };

  const closeDrawer = (drawer, options = {}) => {
    const { restoreFocus = true } = options;
    const state = getState(drawer);

    if (state.closeTimer) {
      clearTimeout(state.closeTimer);
      state.closeTimer = null;
    }

    drawer.classList.remove(OPEN_CLASS);

    state.closeTimer = window.setTimeout(() => {
      hideDrawer(drawer);
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

  const closeOpenedDrawers = (options = {}) => {
    drawers.forEach((drawer) => {
      if (drawer.classList.contains(OPEN_CLASS)) {
        closeDrawer(drawer, options);
      }
    });
  };

  const openDrawer = (drawer, trigger = null) => {
    closeOpenedDrawers({ restoreFocus: false });
    document.dispatchEvent(new CustomEvent(`nice-hair:cart-close`));

    const state = getState(drawer);

    if (state.closeTimer) {
      clearTimeout(state.closeTimer);
      state.closeTimer = null;
    }

    state.previousActiveElement =
      trigger instanceof HTMLElement
        ? trigger
        : document.activeElement instanceof HTMLElement
          ? document.activeElement
          : null;

    drawer.hidden = false;
    void drawer.offsetWidth;
    drawer.classList.add(OPEN_CLASS);
    document.documentElement.style.setProperty(
      `--nh-scrollbar-compensation`,
      `${getScrollbarCompensation()}px`
    );
    document.body.classList.add(BODY_CLASS);

    const closeButton = drawer.querySelector(`[data-nh-content-drawer-close-button]`);

    if (closeButton instanceof HTMLElement) {
      window.requestAnimationFrame(() => {
        closeButton.focus({ preventScroll: true });
      });
    }

    document.dispatchEvent(
      new CustomEvent(`nice-hair:content-drawer-opened`, {
        detail: { drawerId: getDrawerId(drawer) },
      })
    );
  };

  const findDrawerById = (drawerId) =>
    drawers.find((drawer) => getDrawerId(drawer) === drawerId) || null;

  drawers.forEach((drawer) => {
    drawer.querySelectorAll(DRAWER_CLOSE_SELECTOR).forEach((closer) => {
      closer.addEventListener(`click`, () => closeDrawer(drawer));
    });
  });

  document.addEventListener(`click`, (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const trigger = event.target.closest(DRAWER_TRIGGER_SELECTOR);

    if (!trigger) {
      return;
    }

    const drawerId = trigger.getAttribute(`data-nh-content-drawer-target`) || ``;
    const drawer = findDrawerById(drawerId);

    if (!drawer) {
      return;
    }

    event.preventDefault();
    openDrawer(drawer, trigger);
  });

  document.addEventListener(`keydown`, (event) => {
    if (event.key !== `Escape`) {
      return;
    }

    const openedDrawer = drawers.find((drawer) => drawer.classList.contains(OPEN_CLASS));

    if (openedDrawer) {
      closeDrawer(openedDrawer);
    }
  });

  document.addEventListener(`nice-hair:cart-opened`, () => {
    closeOpenedDrawers({ restoreFocus: false });
  });

  document.addEventListener(`nice-hair:content-drawer-close`, (event) => {
    const drawerId = event.detail?.drawerId || ``;
    const drawer = drawerId ? findDrawerById(drawerId) : drawers.find((item) => item.classList.contains(OPEN_CLASS)) || null;

    if (drawer) {
      closeDrawer(drawer);
    }
  });
})();
