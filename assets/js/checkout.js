(function () {
  const form = document.querySelector("form.nh-checkout");
  if (!form || !window.jQuery || typeof window.wc_checkout_params === "undefined") {
    return;
  }

  const $ = window.jQuery;
  const cartUrl = form.dataset.cartUrl || window.location.href;
  const billingCheckbox = form.querySelector("#nh_use_different_billing_address");
  const billingAddress = form.querySelector("[data-billing-address]");
  const advancedToggle = form.querySelector("[data-advanced-toggle]");
  const advancedPanel = form.querySelector("[data-advanced-panel]");
  const inlineErrorClass = "nh-checkout__field-has-error";
  const inlineErrorSelector = ".nh-checkout__field-error";
  const inlineErrorFlag = "data-nh-inline-error";

  const syncBillingVisibility = () => {
    if (!billingCheckbox || !billingAddress) {
      return;
    }

    billingAddress.classList.toggle("is-open", billingCheckbox.checked);
  };

  const shouldForceAdvancedOpen = () => {
    if (billingCheckbox?.checked) {
      return true;
    }

    if (!advancedPanel) {
      return false;
    }

    return Boolean(
      advancedPanel.querySelector(
        ".woocommerce-invalid, .woocommerce-error, .select2-container--focus, .nh-checkout__field-error"
      )
    );
  };

  const syncAdvancedVisibility = () => {
    if (!advancedToggle || !advancedPanel) {
      return;
    }

    const isExpanded = advancedToggle.getAttribute("aria-expanded") === "true";
    const isOpen = isExpanded || shouldForceAdvancedOpen();

    advancedPanel.classList.toggle("is-open", isOpen);
  };

  const openAdvancedPanel = () => {
    if (!advancedToggle || !advancedPanel) {
      return;
    }

    advancedToggle.setAttribute("aria-expanded", "true");
    syncAdvancedVisibility();
  };

  const getFieldRowForTarget = (targetId) => {
    if (!targetId) {
      return null;
    }

    const target = document.getElementById(targetId);

    if (!target) {
      return null;
    }

    if (target.classList.contains("form-row")) {
      return target;
    }

    return target.closest(".form-row");
  };

  const getFieldControl = (row) => {
    if (!row) {
      return null;
    }

    return row.querySelector("input, select, textarea");
  };

  const clearInlineErrorState = (row) => {
    if (!(row instanceof HTMLElement) || row.getAttribute(inlineErrorFlag) !== "1") {
      return;
    }

    row.removeAttribute(inlineErrorFlag);
    row.classList.remove(inlineErrorClass, "woocommerce-invalid");
    row.querySelectorAll(inlineErrorSelector).forEach((node) => node.remove());

    const control = getFieldControl(row);

    if (!control) {
      return;
    }

    control.removeAttribute("aria-invalid");

    const describedBy = (control.getAttribute("aria-describedby") || "")
      .split(/\s+/)
      .filter(Boolean)
      .filter((id) => !id.startsWith("nh-checkout-error-"));

    if (describedBy.length > 0) {
      control.setAttribute("aria-describedby", describedBy.join(" "));
      return;
    }

    control.removeAttribute("aria-describedby");
  };

  const clearInlineFieldErrors = () => {
    form.querySelectorAll(`[${inlineErrorFlag}="1"]`).forEach((row) => {
      clearInlineErrorState(row);
    });
  };

  const buildInlineErrorMessage = (item) => {
    if (!(item instanceof HTMLElement)) {
      return "";
    }

    const clone = item.cloneNode(true);
    clone.querySelectorAll("a").forEach((link) => link.remove());

    let text = clone.textContent.replace(/\s+/g, " ").trim();
    text = text.replace(/^[\s—–\-:,.]+/, "").trim();

    if (text !== "") {
      return text;
    }

    return item.textContent.replace(/\s+/g, " ").trim();
  };

  const setInlineFieldError = (row, message) => {
    if (!(row instanceof HTMLElement) || message === "") {
      return;
    }

    const control = getFieldControl(row);
    const errorIdSource = control?.id || row.id || `field-${Math.random().toString(36).slice(2, 8)}`;
    let errorNode = row.querySelector(inlineErrorSelector);

    if (!errorNode) {
      errorNode = document.createElement("p");
      errorNode.className = "nh-checkout__field-error";
      errorNode.id = `nh-checkout-error-${errorIdSource}`;

      const wrapper = row.querySelector(".woocommerce-input-wrapper");

      if (wrapper instanceof HTMLElement) {
        wrapper.insertAdjacentElement("afterend", errorNode);
      } else {
        row.append(errorNode);
      }
    }

    const messages = errorNode.textContent
      .split("\n")
      .map((entry) => entry.trim())
      .filter(Boolean);

    if (!messages.includes(message)) {
      messages.push(message);
    }

    errorNode.textContent = messages.join(" ");
    row.setAttribute(inlineErrorFlag, "1");
    row.classList.add(inlineErrorClass, "woocommerce-invalid");

    if (control) {
      control.setAttribute("aria-invalid", "true");

      const describedBy = new Set(
        (control.getAttribute("aria-describedby") || "")
          .split(/\s+/)
          .filter(Boolean)
      );

      describedBy.add(errorNode.id);
      control.setAttribute("aria-describedby", Array.from(describedBy).join(" "));
    }

    if (row.closest("[data-advanced-panel]")) {
      openAdvancedPanel();
    }
  };

  const getNoticeRoots = () => {
    return Array.from(
      form.querySelectorAll(".woocommerce-NoticeGroup, ul.woocommerce-error, ul.woocommerce-info, ul.woocommerce-message")
    ).filter((node) => {
      if (!(node instanceof HTMLElement) || node.closest(".form-row")) {
        return false;
      }

      if (node.matches("ul") && node.parentElement?.classList.contains("woocommerce-NoticeGroup")) {
        return false;
      }

      return true;
    });
  };

  const cleanupNoticeRoot = (root) => {
    if (!(root instanceof HTMLElement)) {
      return;
    }

    if (root.matches("ul")) {
      if (!root.querySelector("li")) {
        const noticeGroup = root.closest(".woocommerce-NoticeGroup");
        root.remove();

        if (noticeGroup instanceof HTMLElement && !noticeGroup.querySelector("li")) {
          noticeGroup.remove();
        }
      }

      return;
    }

    if (root.classList.contains("woocommerce-NoticeGroup") && !root.querySelector("li")) {
      root.remove();
    }
  };

  const moveFieldErrorsInline = () => {
    clearInlineFieldErrors();

    getNoticeRoots().forEach((root) => {
      const items = root.matches("ul")
        ? Array.from(root.children).filter((node) => node instanceof HTMLLIElement)
        : Array.from(root.querySelectorAll("li"));

      items.forEach((item) => {
        const link = item.querySelector('a[href^="#"]');
        const href = link?.getAttribute("href") || "";
        const targetId = href.startsWith("#") ? decodeURIComponent(href.slice(1)) : "";
        const row = getFieldRowForTarget(targetId);

        if (!row) {
          return;
        }

        const message = buildInlineErrorMessage(item);
        setInlineFieldError(row, message);
        item.remove();
      });

      cleanupNoticeRoot(root);
    });
  };

  const setCartCount = (count) => {
    document.querySelectorAll(".nh-cart-count").forEach((node) => {
      node.textContent = String(count);
    });
  };

  const updateCartItem = (cartKey, quantity, item) => {
    if (!cartKey) {
      return;
    }

    item?.classList.add("is-loading");

    $.post(
      wc_checkout_params.wc_ajax_url.toString().replace("%%endpoint%%", "update_cart_item"),
      {
        cart_item_key: cartKey,
        quantity,
      }
    )
      .done((response) => {
        if (!response || response.success !== true || !response.data) {
          window.location.reload();
          return;
        }

        setCartCount(response.data.count || 0);

        if ((response.data.count || 0) <= 0) {
          window.location.href = cartUrl;
          return;
        }

        $(document.body).trigger("update_checkout");
      })
      .fail(() => {
        window.location.reload();
      });
  };

  form.addEventListener("click", (event) => {
    const removeLink = event.target.closest(".nh-order-summary__item-remove");

    if (removeLink) {
      event.preventDefault();
      const item = removeLink.closest(".nh-order-summary__item");
      updateCartItem(removeLink.getAttribute("data-cart_item_key"), 0, item);
      return;
    }

    const qtyButton = event.target.closest("[data-nh-order-summary-qty]");

    if (!qtyButton) {
      return;
    }

    event.preventDefault();

    const wrap = qtyButton.closest(".nh-order-summary__qty");
    const item = qtyButton.closest(".nh-order-summary__item");
    const valueEl = wrap?.querySelector(".nh-order-summary__qty-value");

    if (!wrap || !valueEl) {
      return;
    }

    let qty = parseInt(valueEl.textContent || "0", 10) || 0;
    const min = parseInt(wrap.dataset.qtyMin || "0", 10);
    const max = parseInt(wrap.dataset.qtyMax || "", 10);
    const minQty = Number.isNaN(min) ? 0 : min;
    const maxQty = Number.isNaN(max) ? Infinity : max;

    if (qtyButton.dataset.nhOrderSummaryQty === "plus") {
      qty = Math.min(qty + 1, maxQty);
    } else if (qtyButton.dataset.nhOrderSummaryQty === "minus") {
      qty = Math.max(minQty, qty - 1);
    }

    if (qty === (parseInt(valueEl.textContent || "0", 10) || 0)) {
      return;
    }

    updateCartItem(wrap.dataset.cartKey || "", qty, item);
  });

  form.addEventListener("input", (event) => {
    const row = event.target.closest(".form-row");

    if (row) {
      clearInlineErrorState(row);
      syncAdvancedVisibility();
    }
  });

  form.addEventListener("change", (event) => {
    const row = event.target.closest(".form-row");

    if (row) {
      clearInlineErrorState(row);
      syncAdvancedVisibility();
    }
  });

  if (advancedToggle && advancedPanel) {
    advancedToggle.addEventListener("click", () => {
      const isExpanded = advancedToggle.getAttribute("aria-expanded") === "true";

      if (isExpanded && shouldForceAdvancedOpen()) {
        return;
      }

      advancedToggle.setAttribute("aria-expanded", isExpanded ? "false" : "true");
      syncAdvancedVisibility();
    });
  }

  if (billingCheckbox) {
    billingCheckbox.addEventListener("change", () => {
      syncBillingVisibility();

      if (billingCheckbox.checked) {
        openAdvancedPanel();
      } else {
        syncAdvancedVisibility();
      }
    });
  }

  $(document.body).on("updated_checkout", () => {
    syncBillingVisibility();
    syncAdvancedVisibility();
    window.requestAnimationFrame(() => {
      moveFieldErrorsInline();
      syncAdvancedVisibility();
    });
  });

  $(document.body).on("checkout_error", () => {
    window.requestAnimationFrame(() => {
      syncBillingVisibility();
      moveFieldErrorsInline();
      syncAdvancedVisibility();
    });
  });

  syncBillingVisibility();
  syncAdvancedVisibility();
  moveFieldErrorsInline();
})();
