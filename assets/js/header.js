(function () {
  const headers = document.querySelectorAll('.nh-site-header');
  if (!headers.length) return;

  headers.forEach((header) => {
    const toggle = header.querySelector('.nh-site-header__toggle');
    const drawer = header.querySelector('.nh-site-header__drawer');
    if (!toggle || !drawer) return;

    const closers = drawer.querySelectorAll('[data-nh-drawer-close]');
    const drawerLinks = drawer.querySelectorAll('a');
    const cartOpeners = header.querySelectorAll('[data-nh-cart-open]');
    const desktopCatalogItem = header.querySelector('[data-nh-header-desktop-catalog]');
    const desktopCatalogLink = desktopCatalogItem?.querySelector('.nh-site-header__nav-link[aria-haspopup="true"]');
    const accordionItems = Array.from(drawer.querySelectorAll('[data-nh-header-accordion]'));
    let closeTimer = null;

    function syncDesktopCatalog(isOpen) {
      if (!desktopCatalogItem || !desktopCatalogLink) {
        return;
      }

      desktopCatalogItem.classList.toggle('is-open', isOpen);
      desktopCatalogLink.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function closeAccordions() {
      accordionItems.forEach((item) => {
        const button = item.querySelector('[data-nh-header-accordion-toggle]');
        const panel = item.querySelector('[data-nh-header-accordion-panel]');

        if (!button || !panel) {
          return;
        }

        item.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
        panel.hidden = true;
      });
    }

    function openDrawer() {
      if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
      }

      drawer.hidden = false;
      void drawer.offsetWidth;
      drawer.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
      toggle.setAttribute('aria-label', 'Close menu');
      document.body.classList.add('nh-drawer-open');
    }

    function closeDrawer() {
      drawer.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Open menu');
      document.body.classList.remove('nh-drawer-open');
      closeAccordions();

      closeTimer = setTimeout(() => {
        drawer.hidden = true;
      }, 350);
    }

    toggle.addEventListener('click', () => {
      if (toggle.getAttribute('aria-expanded') === 'true') {
        closeDrawer();
      } else {
        openDrawer();
      }
    });

    closers.forEach((el) => el.addEventListener('click', closeDrawer));
    drawerLinks.forEach((a) => a.addEventListener('click', closeDrawer));
    cartOpeners.forEach((button) =>
      button.addEventListener('click', () => {
        if (drawer.contains(button) && toggle.getAttribute('aria-expanded') === 'true') {
          closeDrawer();
          window.setTimeout(() => {
            document.dispatchEvent(new CustomEvent('nice-hair:cart-open'));
          }, 40);
          return;
        }

        document.dispatchEvent(new CustomEvent('nice-hair:cart-open'));
      })
    );

    if (desktopCatalogItem && desktopCatalogLink) {
      desktopCatalogItem.addEventListener('mouseenter', () => syncDesktopCatalog(true));
      desktopCatalogItem.addEventListener('mouseleave', () => syncDesktopCatalog(false));
      desktopCatalogItem.addEventListener('focusin', () => syncDesktopCatalog(true));
      desktopCatalogItem.addEventListener('focusout', (event) => {
        if (desktopCatalogItem.contains(event.relatedTarget)) {
          return;
        }

        syncDesktopCatalog(false);
      });
    }

    accordionItems.forEach((item) => {
      const button = item.querySelector('[data-nh-header-accordion-toggle]');
      const panel = item.querySelector('[data-nh-header-accordion-panel]');

      if (!button || !panel) {
        return;
      }

      button.addEventListener('click', (event) => {
        event.preventDefault();

        const isExpanded = button.getAttribute('aria-expanded') === 'true';
        closeAccordions();

        if (!isExpanded) {
          item.classList.add('is-open');
          button.setAttribute('aria-expanded', 'true');
          panel.hidden = false;
        }
      });
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        closeDrawer();
      }
    });
  });
})();
