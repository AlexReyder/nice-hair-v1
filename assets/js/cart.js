(function () {
  const drawer = document.getElementById("nh-cart-drawer");
  const getPageCart = () => document.querySelector(".nh-cart[data-wc-ajax-url]");
  const hasCartUI = Boolean(drawer || getPageCart());

  if (!hasCartUI || !window.jQuery) {
    return;
  }

  const $ = window.jQuery;
  const panel = drawer?.querySelector(".nh-cart-drawer__panel") || null;
  const closers = drawer?.querySelectorAll("[data-nh-cart-close]") || [];
  let closeTimer = null;

  function getAjaxUrlTemplate() {
    const pageCart = getPageCart();

    if (pageCart?.dataset.wcAjaxUrl) {
      return pageCart.dataset.wcAjaxUrl;
    }

    if (typeof window.wc_cart_fragments_params !== "undefined" && window.wc_cart_fragments_params.wc_ajax_url) {
      return window.wc_cart_fragments_params.wc_ajax_url.toString();
    }

    if (typeof window.wc_checkout_params !== "undefined" && window.wc_checkout_params.wc_ajax_url) {
      return window.wc_checkout_params.wc_ajax_url.toString();
    }

    return "";
  }

  function getAjaxUrl(endpoint) {
    const template = getAjaxUrlTemplate();
    return template ? template.replace("%%endpoint%%", endpoint) : "";
  }

  function setCartCount(count) {
    document.querySelectorAll(".nh-cart-count").forEach((node) => {
      node.textContent = String(count);
    });
  }

  function open() {
    if (!drawer) {
      return;
    }

    if (closeTimer) {
      clearTimeout(closeTimer);
      closeTimer = null;
    }

    drawer.hidden = false;
    void drawer.offsetWidth;
    drawer.classList.add("is-open");
    document.body.classList.add("nh-cart-drawer-open");
    document.dispatchEvent(new CustomEvent("nice-hair:cart-opened"));
  }

  function close() {
    if (!drawer) {
      return;
    }

    if (closeTimer) {
      clearTimeout(closeTimer);
      closeTimer = null;
    }

    drawer.classList.remove("is-open");
    document.body.classList.remove("nh-cart-drawer-open");
    closeTimer = setTimeout(() => {
      drawer.hidden = true;
    }, 350);
  }

  function replacePageCart(html) {
    const currentCart = getPageCart();

    if (!currentCart || typeof html !== "string" || html.trim() === "") {
      return false;
    }

    const parsed = new DOMParser().parseFromString(html, "text/html");
    const nextCart = parsed.querySelector(".nh-cart[data-wc-ajax-url]");

    if (!nextCart) {
      return false;
    }

    currentCart.replaceWith(nextCart);
    return true;
  }

  function refreshPageCartFallback() {
    const currentCart = getPageCart();

    if (!currentCart) {
      window.location.reload();
      return;
    }

    fetch(window.location.href, {
      credentials: "same-origin",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.text())
      .then((html) => {
        if (!replacePageCart(html)) {
          window.location.reload();
        }
      })
      .catch(() => {
        window.location.reload();
      });
  }

  function handlePageCartResponse(response) {
    if (!response || response.success !== true || !response.data) {
      refreshPageCartFallback();
      return;
    }

    setCartCount(response.data.count || 0);

    if (!replacePageCart(response.data.cart_html || "")) {
      refreshPageCartFallback();
    }
  }

  function handleDrawerResponse(response, cartKey) {
    if (!response || response.success !== true || !response.data) {
      window.location.reload();
      return;
    }

    setCartCount(response.data.count || 0);

    const refreshUrl = getAjaxUrl("get_refreshed_fragments");

    if (!refreshUrl) {
      window.location.reload();
      return;
    }

    if (response.data.count <= 0) {
      $(document.body).trigger("wc_fragment_refresh");
      return;
    }

    $(document.body).trigger("wc_fragment_refresh");

    const newWrap = drawer?.querySelector('.nh-minicart__qty[data-cart-key="' + cartKey + '"]');
    if (newWrap) {
      newWrap.classList.remove("is-loading");
    }
  }

  function updateCartItem(cartKey, quantity, item) {
    const url = getAjaxUrl("update_cart_item");
    const pageCart = getPageCart();

    if (!url || !cartKey) {
      window.location.reload();
      return;
    }

    item?.classList.add("is-loading");

    $.post(url, {
      cart_item_key: cartKey,
      quantity,
    })
      .done((response) => {
        if (pageCart) {
          handlePageCartResponse(response);
          return;
        }

        handleDrawerResponse(response, cartKey);
      })
      .fail(() => {
        if (pageCart) {
          refreshPageCartFallback();
          return;
        }

        window.location.reload();
      })
      .always(() => {
        if (pageCart) {
          return;
        }

        const newItem = drawer?.querySelector(
          '.nh-minicart__item-remove[data-cart_item_key="' + cartKey + '"]'
        )?.closest(".nh-minicart__item");

        if (newItem) {
          newItem.classList.remove("is-loading");
        }
      });
  }

  closers.forEach((el) => el.addEventListener("click", close));

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && drawer && !drawer.hidden) {
      close();
    }
  });

  $(document.body).on("added_to_cart", function () {
    open();
  });

  document.addEventListener("nice-hair:cart-open", open);
  document.addEventListener("nice-hair:cart-close", close);

  document.addEventListener("click", function (e) {
    const removeLink = e.target.closest(".nh-minicart__item-remove, .nh-cart .nh-order-summary__item-remove");

    if (removeLink) {
      e.preventDefault();

      const cartKey = removeLink.getAttribute("data-cart_item_key");
      const item = removeLink.closest(".nh-minicart__item, .nh-cart .nh-order-summary__item");

      if (!cartKey || !item) {
        return;
      }

      updateCartItem(cartKey, 0, item);
      return;
    }

    const btn = e.target.closest("[data-nh-qty], .nh-cart [data-nh-order-summary-qty]");
    if (!btn) {
      return;
    }

    const wrap = btn.closest(".nh-minicart__qty, .nh-cart .nh-order-summary__qty");
    const item = btn.closest(".nh-minicart__item, .nh-cart .nh-order-summary__item");
    const valueEl = wrap?.querySelector(".nh-minicart__qty-value, .nh-order-summary__qty-value");

    if (!wrap || !valueEl) {
      return;
    }

    const cartKey = wrap.dataset.cartKey;
    let qty = parseInt(valueEl.textContent || "0", 10) || 0;
    const currentQty = qty;
    const min = parseInt(wrap.dataset.qtyMin || "0", 10);
    const max = parseInt(wrap.dataset.qtyMax || "", 10);
    const minQty = Number.isNaN(min) ? 0 : min;
    const maxQty = Number.isNaN(max) ? Infinity : max;

    const direction = btn.dataset.nhQty || btn.dataset.nhOrderSummaryQty;

    if (direction === "plus") {
      qty = Math.min(qty + 1, maxQty);
    } else if (direction === "minus") {
      qty = Math.max(minQty, qty - 1);
    }

    if (!cartKey || qty === currentQty) {
      return;
    }

    updateCartItem(cartKey, qty, item);
  });
})();
