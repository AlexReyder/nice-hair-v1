(function () {
  const form = document.querySelector(
    ".nh-single-product__cart-form, .nh-single-product form.cart",
  );
  const singleProduct = form
    ? form.closest(".nh-single-product")
    : document.querySelector(".nh-single-product");

  if (!singleProduct) {
    return;
  }

  const qtyInput = form ? form.querySelector('input[name="quantity"]') : null;
  const qtyWrap = form ? form.querySelector(".nh-single-product__qty") : null;
  const gallery = document.querySelector(".nh-single-product__gallery");
  const mainImg = gallery
    ? gallery.querySelector(".nh-single-product__main-img")
    : null;
  const thumbs = gallery
    ? gallery.querySelectorAll(".nh-single-product__thumb")
    : [];
  const galleryTrigger = gallery
    ? gallery.querySelector("[data-nh-sp-gallery-trigger]")
    : null;
  const lightboxSource = gallery
    ? gallery.querySelector("[data-nh-sp-lightbox-source]")
    : null;

  function setQuantityAvailability(enabled) {
    if (!qtyWrap) return;

    qtyWrap
      .querySelectorAll(
        ".nh-single-product__qty-btn, .nh-single-product__qty-input",
      )
      .forEach(function (control) {
        control.disabled = !enabled;
      });
  }

  const simpleQuantityPriceBox = singleProduct.querySelector(
    "[data-nh-sp-price][data-nh-sp-unit-price]",
  );

  function escapePriceHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function formatNumberForWooPrice(amount, priceBox) {
    const decimals = parseInt(priceBox.dataset.nhSpPriceDecimals || "2", 10);
    const safeDecimals = Number.isFinite(decimals) ? decimals : 2;
    const decimalSeparator = priceBox.dataset.nhSpDecimalSeparator || ".";
    const thousandSeparator = priceBox.dataset.nhSpThousandSeparator || ",";

    const fixed = Number(amount).toFixed(safeDecimals);
    const parts = fixed.split(".");
    const integerPart = parts[0].replace(
      /\B(?=(\d{3})+(?!\d))/g,
      thousandSeparator,
    );
    const decimalPart = parts[1] || "";

    return decimalPart
      ? integerPart + decimalSeparator + decimalPart
      : integerPart;
  }

  function formatSingleProductPriceHtml(amount, priceBox) {
    const numericAmount = Number(amount);

    if (!Number.isFinite(numericAmount)) {
      return "";
    }

    const currencySymbol = priceBox.dataset.nhSpCurrencySymbol || "$";
    const priceFormat = priceBox.dataset.nhSpPriceFormat || "%2$s&nbsp;%1$s";
    const formattedNumber = formatNumberForWooPrice(numericAmount, priceBox);

    const currencyHtml =
      '<span class="woocommerce-Price-currencySymbol">' +
      escapePriceHtml(currencySymbol) +
      "</span>";

    const priceInnerHtml = priceFormat
      .replace("%1$s", currencyHtml)
      .replace("%2$s", escapePriceHtml(formattedNumber));

    return (
      '<span class="woocommerce-Price-amount amount"><bdi>' +
      priceInnerHtml +
      "</bdi></span>"
    );
  }

  function setQuantityPriceDataFromOption(option) {
    const priceBox = singleProduct.querySelector("[data-nh-sp-price]");

    if (!priceBox || !option) {
      return;
    }

    const unitPrice = option.dataset.unitPrice || "";
    const unitRegularPrice = option.dataset.unitRegularPrice || "";
    const originalPriceHtml =
      option.dataset.originalPriceHtml ||
      option.getAttribute("data-price-html") ||
      "";

    if (!unitPrice) {
      delete priceBox.dataset.nhSpUnitPrice;
      delete priceBox.dataset.nhSpUnitRegularPrice;
      delete priceBox.dataset.nhSpOriginalPriceHtml;
      return;
    }

    priceBox.dataset.nhSpUnitPrice = unitPrice;
    priceBox.dataset.nhSpUnitRegularPrice = unitRegularPrice;
    priceBox.dataset.nhSpOriginalPriceHtml = originalPriceHtml;
  }

  function updateSimpleQuantityPrice() {
    const priceBox = singleProduct.querySelector(
      "[data-nh-sp-price][data-nh-sp-unit-price]",
    );

    if (!priceBox || !qtyInput) {
      return;
    }

    const unitPrice = Number(priceBox.dataset.nhSpUnitPrice);

    if (!Number.isFinite(unitPrice)) {
      return;
    }

    const min = parseInt(qtyInput.min, 10) || 1;
    const max = parseInt(qtyInput.max, 10);
    const maxValue = Number.isFinite(max) ? max : Infinity;

    let quantity = parseInt(qtyInput.value, 10) || min;
    quantity = Math.max(min, Math.min(quantity, maxValue));

    qtyInput.value = String(quantity);

    const originalPriceHtml = priceBox.dataset.nhSpOriginalPriceHtml || "";

    if (quantity === 1 && originalPriceHtml) {
      priceBox.innerHTML = originalPriceHtml;
      return;
    }

    const unitRegularPrice = Number(
      priceBox.dataset.nhSpUnitRegularPrice || "",
    );
    const totalPrice = unitPrice * quantity;

    if (Number.isFinite(unitRegularPrice) && unitRegularPrice > unitPrice) {
      priceBox.innerHTML =
        "<del>" +
        formatSingleProductPriceHtml(unitRegularPrice * quantity, priceBox) +
        "</del> " +
        "<ins>" +
        formatSingleProductPriceHtml(totalPrice, priceBox) +
        "</ins>";

      return;
    }

    priceBox.innerHTML = formatSingleProductPriceHtml(totalPrice, priceBox);
  }

  function nativeSubmit(targetForm) {
    targetForm.dataset.nhNativeSubmit = "1";
    targetForm.submit();
  }

  function setActiveGalleryIndex(index) {
    if (!galleryTrigger) return;

    galleryTrigger.dataset.activeIndex = String(index);
  }

  function initLightboxSource(attempt) {
    if (!lightboxSource || typeof jQuery === "undefined") return null;

    const $lightboxSource = jQuery(lightboxSource);
    const existingGallery = $lightboxSource.data("product_gallery");

    if (existingGallery) {
      return existingGallery;
    }

    if (typeof $lightboxSource.wc_product_gallery !== "function") {
      if ((attempt || 0) >= 40) {
        return null;
      }

      window.setTimeout(function () {
        initLightboxSource((attempt || 0) + 1);
      }, 50);

      return null;
    }

    $lightboxSource.wc_product_gallery({
      flexslider_enabled: false,
      zoom_enabled: false,
      photoswipe_enabled: true,
    });

    return $lightboxSource.data("product_gallery") || null;
  }

  function openLightbox(attempt) {
    if (!galleryTrigger || !lightboxSource || typeof jQuery === "undefined") {
      return;
    }

    const $lightboxSource = jQuery(lightboxSource);
    const productGallery =
      $lightboxSource.data("product_gallery") || initLightboxSource(0);
    const sourceLinks = lightboxSource.querySelectorAll(
      ".woocommerce-product-gallery__image a",
    );
    const activeIndex = parseInt(galleryTrigger.dataset.activeIndex, 10) || 0;
    const activeLink = sourceLinks[activeIndex] || sourceLinks[0];

    if (!productGallery && (attempt || 0) < 10) {
      window.setTimeout(function () {
        openLightbox((attempt || 0) + 1);
      }, 50);
      return;
    }

    if (
      !productGallery ||
      typeof productGallery.openPhotoswipe !== "function" ||
      !activeLink
    ) {
      return;
    }

    productGallery.openPhotoswipe({
      preventDefault: function () {},
      currentTarget: galleryTrigger,
      target: activeLink,
    });
  }

  if (galleryTrigger) {
    galleryTrigger.addEventListener("click", function () {
      openLightbox(0);
    });

    galleryTrigger.addEventListener("keydown", function (event) {
      if (event.key !== "Enter" && event.key !== " ") return;

      event.preventDefault();
      openLightbox(0);
    });
  }

  initLightboxSource(0);

  if (mainImg && thumbs.length > 0) {
    thumbs.forEach(function (thumb) {
      thumb.addEventListener("click", function () {
        const fullSrc = thumb.dataset.fullSrc;
        const galleryIndex = parseInt(thumb.dataset.galleryIndex, 10) || 0;
        if (!fullSrc) return;

        mainImg.src = fullSrc;
        mainImg.dataset.fullSrc = fullSrc;
        mainImg.removeAttribute("srcset");
        setActiveGalleryIndex(galleryIndex);

        thumbs.forEach(function (item) {
          item.classList.remove("is-active");
        });
        thumb.classList.add("is-active");
      });
    });
  }

  function initScrollableRail(scrollList, rail) {
    const railLine = rail
      ? rail.querySelector(".nh-single-product__product-form-rail-line")
      : null;

    if (!scrollList || !rail || !railLine) {
      return;
    }

    let isDragging = false;
    let thumbWidth = 0;

    function getMaxScroll() {
      return Math.max(0, scrollList.scrollWidth - scrollList.clientWidth);
    }

    function updateRail() {
      const maxScroll = getMaxScroll();
      const trackWidth = railLine.clientWidth;
      const isScrollable = maxScroll > 1 && trackWidth > 0;

      rail.classList.toggle("is-scrollable", isScrollable);

      if (!isScrollable) {
        thumbWidth = trackWidth;
        railLine.style.setProperty("--nh-sp-scroll-thumb-width", "100%");
        railLine.style.setProperty("--nh-sp-scroll-thumb-left", "0px");
        return;
      }

      thumbWidth = Math.max(
        36,
        Math.round(
          (scrollList.clientWidth / scrollList.scrollWidth) * trackWidth,
        ),
      );
      thumbWidth = Math.min(thumbWidth, trackWidth);

      const travel = Math.max(1, trackWidth - thumbWidth);
      const thumbLeft = Math.round(
        (scrollList.scrollLeft / maxScroll) * travel,
      );

      railLine.style.setProperty(
        "--nh-sp-scroll-thumb-width",
        thumbWidth + "px",
      );
      railLine.style.setProperty("--nh-sp-scroll-thumb-left", thumbLeft + "px");
    }

    function scrollFromPointer(clientX) {
      const maxScroll = getMaxScroll();
      const trackWidth = railLine.clientWidth;
      const travel = Math.max(1, trackWidth - thumbWidth);
      const rect = railLine.getBoundingClientRect();
      const nextLeft = Math.min(
        travel,
        Math.max(0, clientX - rect.left - thumbWidth / 2),
      );

      scrollList.scrollLeft = (nextLeft / travel) * maxScroll;
    }

    scrollList.addEventListener("scroll", updateRail, { passive: true });
    window.addEventListener("resize", updateRail);

    rail.addEventListener("pointerdown", function (event) {
      if (!rail.classList.contains("is-scrollable")) {
        return;
      }

      isDragging = true;
      rail.classList.add("is-dragging");
      rail.setPointerCapture?.(event.pointerId);
      scrollFromPointer(event.clientX);
      event.preventDefault();
    });

    rail.addEventListener("pointermove", function (event) {
      if (!isDragging) {
        return;
      }

      scrollFromPointer(event.clientX);
      event.preventDefault();
    });

    function stopDragging(event) {
      if (!isDragging) {
        return;
      }

      isDragging = false;
      rail.classList.remove("is-dragging");
      rail.releasePointerCapture?.(event.pointerId);
    }

    rail.addEventListener("pointerup", stopDragging);
    rail.addEventListener("pointercancel", stopDragging);

    window.requestAnimationFrame(updateRail);
    window.addEventListener("load", updateRail, { once: true });
  }

  singleProduct
    .querySelectorAll(
      ".nh-single-product__product-form-panel, .nh-single-product__color-shell",
    )
    .forEach(function (shell) {
      const scrollList = shell.querySelector(
        ".nh-single-product__product-form-list, .nh-single-product__color-list",
      );
      const rail = shell.querySelector(".nh-single-product__product-form-rail");

      initScrollableRail(scrollList, rail);
    });

  if (!form) {
    return;
  }

  form.addEventListener("click", function (event) {
    const btn = event.target.closest("[data-nh-sp-qty]");
    if (!btn || !qtyInput || btn.disabled) return;

    let qty = parseInt(qtyInput.value, 10) || 1;
    const min = parseInt(qtyInput.min, 10) || 1;
    const max = parseInt(qtyInput.max, 10) || Infinity;

    if (btn.dataset.nhSpQty === "plus") {
      qty = Math.min(qty + 1, max);
    } else if (btn.dataset.nhSpQty === "minus") {
      qty = Math.max(qty - 1, min);
    }

    qtyInput.value = String(qty);
    updateSimpleQuantityPrice();
  });

  if (qtyInput) {
    ["input", "change", "blur"].forEach(function (eventName) {
      qtyInput.addEventListener(eventName, updateSimpleQuantityPrice);
    });
  }

  const priceBox = singleProduct.querySelector("[data-nh-sp-price]");
  const skuWrap = singleProduct.querySelector("[data-nh-sp-sku-wrap]");
  const skuValue = singleProduct.querySelector("[data-nh-sp-sku]");
  const variationOptions =
    singleProduct.querySelectorAll("[data-nh-variation-option]") || [];
  const productFormOptions =
    singleProduct.querySelectorAll("[data-nh-product-form-option]") || [];
  const customConfigNode = singleProduct.querySelector(
    "[data-nh-custom-config]",
  );
  const variationIdInput = form.querySelector(
    "[data-nh-variation-id], input[name='variation_id']",
  );
  const productFormInput = form.querySelector("[data-nh-product-form]");
  const productFormPreviewMedia = singleProduct.querySelector(
    "[data-nh-product-form-preview-media]",
  );
  const productFormPreviewImage = singleProduct.querySelector(
    "[data-nh-product-form-preview-image]",
  );
  const productFormSupportCopy = singleProduct.querySelector(
    "[data-nh-product-form-support-copy]",
  );
  const variationAttributeInputs = form.querySelectorAll(
    "[data-nh-variation-attribute]",
  );
  const addButtons = form.querySelectorAll("[data-nh-atc-submit]");

  function setAddButtonsAvailability(enabled) {
    addButtons.forEach(function (button) {
      button.disabled = !enabled;
    });

    setQuantityAvailability(enabled);
  }

  function updatePriceMarkup(html) {
    if (!priceBox) return;

    if (html) {
      priceBox.innerHTML = html;
      priceBox.classList.remove("nh-single-product__price-request");
      priceBox.classList.add("nh-single-product__price");
    } else {
      priceBox.textContent = "Price on request";
      priceBox.classList.remove("nh-single-product__price");
      priceBox.classList.add("nh-single-product__price-request");
    }
  }

  function formatPriceHtml(amount) {
    const numericAmount = Number(amount);

    if (!Number.isFinite(numericAmount)) {
      return "";
    }

    return (
      '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' +
      numericAmount.toFixed(2) +
      "</bdi></span>"
    );
  }

  function clampQuantityValue(maxValue) {
    if (!qtyInput) return;

    const minValue = parseInt(qtyInput.min, 10) || 1;
    let nextValue = parseInt(qtyInput.value, 10) || minValue;

    if (maxValue > 0) {
      nextValue = Math.min(nextValue, maxValue);
      qtyInput.max = String(maxValue);
    } else {
      qtyInput.removeAttribute("max");
    }

    nextValue = Math.max(nextValue, minValue);
    qtyInput.value = String(nextValue);
  }

  function setActiveVariation(option) {
    if (!option) return;

    variationOptions.forEach(function (item) {
      item.classList.remove("is-active");
    });

    option.classList.add("is-active");

    if (variationIdInput) {
      variationIdInput.value = option.dataset.variationId || "";
    }

    variationAttributeInputs.forEach(function (input) {
      if (input.dataset.nhVariationAttribute === "attribute_pa_weight") {
        input.value = option.dataset.weightValue || "";
      }
    });

    updatePriceMarkup(option.getAttribute("data-price-html") || "");
    setQuantityPriceDataFromOption(option);
    updateSimpleQuantityPrice();

    if (skuWrap && skuValue) {
      const nextSku = option.dataset.sku || "";
      skuValue.textContent = nextSku;
      skuWrap.hidden = !nextSku;
    }

    const isPurchasable = option.dataset.purchasable === "1";
    const maxQty = parseInt(option.dataset.maxQty || "", 10) || 0;

    setAddButtonsAvailability(isPurchasable);
    clampQuantityValue(maxQty);
  }

  function setActiveProductForm(option) {
    if (!option) return;

    productFormOptions.forEach(function (item) {
      item.classList.remove("is-active");
      item.setAttribute("aria-pressed", "false");
    });

    option.classList.add("is-active");
    option.setAttribute("aria-pressed", "true");

    if (productFormInput) {
      productFormInput.value = option.dataset.formKey || "";
    }

    updatePriceMarkup(option.getAttribute("data-price-html") || "");

    if (productFormPreviewImage && productFormPreviewMedia) {
      const sampleSrc = option.dataset.sampleSrc || "";
      const sampleAlt = option.dataset.sampleAlt || "";

      if (sampleSrc) {
        productFormPreviewImage.src = sampleSrc;
        productFormPreviewImage.alt = sampleAlt;
        productFormPreviewMedia.hidden = false;
      } else {
        productFormPreviewMedia.hidden = true;
      }
    }

    if (productFormSupportCopy) {
      const supportCopy = option.dataset.supportCopy || "";
      productFormSupportCopy.textContent = supportCopy;
      productFormSupportCopy.hidden = !supportCopy;
    }

    setAddButtonsAvailability(option.dataset.available === "1");
  }

  function initializeCustomConfigurator() {
    if (!customConfigNode) {
      return false;
    }

    let config;

    try {
      config = JSON.parse(customConfigNode.textContent || "{}");
    } catch (error) {
      setAddButtonsAvailability(false);
      return true;
    }

    const colorOptions = singleProduct.querySelectorAll(
      "[data-nh-custom-color-option]",
    );
    const choiceButtons = singleProduct.querySelectorAll(
      "[data-nh-custom-choice]",
    );
    const weightInput = form.querySelector("[data-nh-custom-weight-input]");
    const weightHiddenInput = form.querySelector(
      "[data-nh-custom-weight-hidden]",
    );
    const colorInput = form.querySelector("[data-nh-custom-color-input]");
    const lengthInput = form.querySelector("[data-nh-custom-length-input]");
    const qualityInput = form.querySelector("[data-nh-custom-quality-input]");
    const textureInput = form.querySelector("[data-nh-custom-texture-input]");

    const allowedValues = {
      length: Array.from(
        singleProduct.querySelectorAll(
          '[data-nh-custom-choice-group="length"]',
        ),
      ).map(function (button) {
        return button.dataset.value || "";
      }),
      quality: Array.from(
        singleProduct.querySelectorAll(
          '[data-nh-custom-choice-group="quality"]',
        ),
      ).map(function (button) {
        return button.dataset.value || "";
      }),
      texture: Array.from(
        singleProduct.querySelectorAll(
          '[data-nh-custom-choice-group="texture"]',
        ),
      ).map(function (button) {
        return button.dataset.value || "";
      }),
    };

    function normalizeWeight(rawValue) {
      const min = parseInt(config.weight?.min, 10) || 30;
      const step = parseInt(config.weight?.step, 10) || 10;
      const fallback = parseInt(config.weight?.default, 10) || min;
      let value = parseInt(rawValue, 10);

      if (!Number.isFinite(value)) {
        value = fallback;
      }

      value = Math.max(value, min);
      const rounded = Math.round((value - min) / step) * step + min;

      return Math.max(rounded, min);
    }

    function updateMainImage(colorKey) {
      if (!mainImg) return;

      const colorConfig = Array.isArray(config.colors)
        ? config.colors.find(function (item) {
            return item && item.key === colorKey;
          })
        : null;
      const mainImage =
        colorConfig && colorConfig.mainImage ? colorConfig.mainImage : null;

      if (!mainImage || !mainImage.url) {
        return;
      }

      mainImg.src = mainImage.url;
      mainImg.dataset.fullSrc = mainImage.fullUrl || mainImage.url;
      mainImg.alt = mainImage.alt || mainImg.alt || "";
      mainImg.removeAttribute("srcset");

      if (Number.isInteger(mainImage.lightboxIndex)) {
        setActiveGalleryIndex(mainImage.lightboxIndex);
      }

      thumbs.forEach(function (thumb) {
        thumb.classList.remove("is-active");
        if (thumb.dataset.fullSrc === mainImage.url) {
          thumb.classList.add("is-active");
        }
      });
    }

    function markActiveChoices(state) {
      colorOptions.forEach(function (button) {
        const isActive = (button.dataset.colorKey || "") === state.color;
        button.classList.toggle("is-active", isActive);
        button.setAttribute("aria-pressed", isActive ? "true" : "false");
      });

      choiceButtons.forEach(function (button) {
        const group = button.dataset.nhCustomChoiceGroup || "";
        const value = button.dataset.value || "";
        const isActive = state[group] === value;
        button.classList.toggle("is-active", isActive);
      });
    }

    function calculatePrice(state) {
      const qualityMap = config.basePriceMap?.[state.quality] || null;
      const basePrice = qualityMap ? qualityMap[state.length] : null;
      const surcharge = Number(config.productForm?.surchargePerGram);

      if (typeof basePrice !== "number" || !Number.isFinite(basePrice)) {
        return null;
      }

      if (!Number.isFinite(surcharge)) {
        return null;
      }

      return Number(((basePrice + surcharge) * state.weight).toFixed(2));
    }

    function renderState(nextState, options) {
      const shouldUpdateImage = !options || options.updateImage !== false;
      const state = {
        color:
          nextState.color && colorInput
            ? nextState.color
            : config.selections?.color || "",
        length: allowedValues.length.includes(nextState.length)
          ? nextState.length
          : config.selections?.length || allowedValues.length[0] || "",
        quality: allowedValues.quality.includes(nextState.quality)
          ? nextState.quality
          : config.selections?.quality || allowedValues.quality[0] || "",
        texture: allowedValues.texture.includes(nextState.texture)
          ? nextState.texture
          : config.selections?.texture || allowedValues.texture[0] || "",
        weight: normalizeWeight(nextState.weight),
      };

      if (colorInput) {
        const allowedColorKeys = Array.from(colorOptions).map(
          function (button) {
            return button.dataset.colorKey || "";
          },
        );

        if (!allowedColorKeys.includes(state.color)) {
          state.color = allowedColorKeys[0] || "";
        }
      }

      if (colorInput) colorInput.value = state.color;
      if (lengthInput) lengthInput.value = state.length;
      if (qualityInput) qualityInput.value = state.quality;
      if (textureInput) textureInput.value = state.texture;
      if (weightInput) weightInput.value = String(state.weight);
      if (weightHiddenInput) weightHiddenInput.value = String(state.weight);

      markActiveChoices(state);
      if (shouldUpdateImage) {
        updateMainImage(state.color);
      }

      const price = calculatePrice(state);

      if (price !== null) {
        updatePriceMarkup(formatPriceHtml(price));
      } else {
        updatePriceMarkup("");
      }

      setAddButtonsAvailability(price !== null);
    }

    const initialState = {
      color: colorInput?.value || config.selections?.color || "",
      length: lengthInput?.value || config.selections?.length || "",
      quality: qualityInput?.value || config.selections?.quality || "",
      texture: textureInput?.value || config.selections?.texture || "",
      weight:
        weightHiddenInput?.value ||
        weightInput?.value ||
        config.selections?.weight ||
        config.weight?.default ||
        30,
    };

    colorOptions.forEach(function (button) {
      button.addEventListener("click", function (event) {
        event.preventDefault();

        renderState({
          color: button.dataset.colorKey || "",
          length: lengthInput?.value || "",
          quality: qualityInput?.value || "",
          texture: textureInput?.value || "",
          weight: weightHiddenInput?.value || weightInput?.value || "",
        });
      });
    });

    choiceButtons.forEach(function (button) {
      button.addEventListener("click", function (event) {
        event.preventDefault();

        const group = button.dataset.nhCustomChoiceGroup || "";
        if (!group) return;

        renderState(
          {
            color: colorInput?.value || "",
            length:
              group === "length"
                ? button.dataset.value || ""
                : lengthInput?.value || "",
            quality:
              group === "quality"
                ? button.dataset.value || ""
                : qualityInput?.value || "",
            texture:
              group === "texture"
                ? button.dataset.value || ""
                : textureInput?.value || "",
            weight: weightHiddenInput?.value || weightInput?.value || "",
          },
          { updateImage: false },
        );
      });
    });

    singleProduct
      .querySelectorAll("[data-nh-custom-weight]")
      .forEach(function (button) {
        button.addEventListener("click", function (event) {
          event.preventDefault();

          const step = parseInt(config.weight?.step, 10) || 10;
          const currentWeight = normalizeWeight(
            weightHiddenInput?.value || weightInput?.value,
          );
          const nextWeight =
            button.dataset.nhCustomWeight === "minus"
              ? currentWeight - step
              : currentWeight + step;

          renderState(
            {
              color: colorInput?.value || "",
              length: lengthInput?.value || "",
              quality: qualityInput?.value || "",
              texture: textureInput?.value || "",
              weight: nextWeight,
            },
            { updateImage: false },
          );
        });
      });

    if (weightInput) {
      weightInput.addEventListener("keydown", function (event) {
        if (event.key !== "Enter") {
          return;
        }

        event.preventDefault();

        renderState(
          {
            color: colorInput?.value || "",
            length: lengthInput?.value || "",
            quality: qualityInput?.value || "",
            texture: textureInput?.value || "",
            weight: weightInput.value,
          },
          { updateImage: false },
        );
      });

      ["change", "blur"].forEach(function (eventName) {
        weightInput.addEventListener(eventName, function () {
          renderState(
            {
              color: colorInput?.value || "",
              length: lengthInput?.value || "",
              quality: qualityInput?.value || "",
              texture: textureInput?.value || "",
              weight: weightInput.value,
            },
            { updateImage: false },
          );
        });
      });
    }

    renderState(initialState, { updateImage: false });

    return true;
  }

  if (variationOptions.length > 0) {
    let activeOption = singleProduct.querySelector(
      "[data-nh-variation-option].is-active:not(.is-disabled)",
    );

    if (!activeOption) {
      activeOption = singleProduct.querySelector(
        "[data-nh-variation-option]:not(.is-disabled)",
      );
    }

    if (!activeOption) {
      activeOption = singleProduct.querySelector("[data-nh-variation-option]");
    }

    if (activeOption) {
      setActiveVariation(activeOption);
    } else {
      setAddButtonsAvailability(false);
    }

    variationOptions.forEach(function (option) {
      option.addEventListener("click", function () {
        if (option.disabled || option.classList.contains("is-disabled")) {
          return;
        }

        setActiveVariation(option);
      });
    });
  } else if (productFormOptions.length > 0) {
    let activeOption = singleProduct.querySelector(
      "[data-nh-product-form-option].is-active:not(.is-disabled)",
    );

    if (!activeOption) {
      activeOption = singleProduct.querySelector(
        "[data-nh-product-form-option]:not(.is-disabled)",
      );
    }

    if (!activeOption) {
      activeOption = singleProduct.querySelector(
        "[data-nh-product-form-option]",
      );
    }

    if (activeOption) {
      setActiveProductForm(activeOption);
    } else {
      setAddButtonsAvailability(false);
    }

    productFormOptions.forEach(function (option) {
      option.addEventListener("click", function () {
        if (option.disabled || option.classList.contains("is-disabled")) {
          return;
        }

        setActiveProductForm(option);
      });
    });
  } else if (!initializeCustomConfigurator()) {
    setAddButtonsAvailability(addButtons.length > 0);
  }

  form.addEventListener("submit", function (event) {
    if (form.dataset.nhNativeSubmit === "1") {
      delete form.dataset.nhNativeSubmit;
      return;
    }

    if (typeof wc_cart_fragments_params === "undefined") return;

    if (variationIdInput && !variationIdInput.value) {
      return;
    }

    if (productFormInput && !productFormInput.value) {
      return;
    }

    event.preventDefault();

    addButtons.forEach(function (button) {
      button.classList.add("is-loading");
      button.disabled = true;
    });

    const data = new URLSearchParams(new FormData(form));
    const submitter =
      event.submitter instanceof HTMLElement
        ? event.submitter
        : document.activeElement instanceof HTMLElement
          ? document.activeElement
          : null;
    const submitterProductId =
      submitter && submitter.getAttribute("name") === "add-to-cart"
        ? submitter.getAttribute("value") || ""
        : "";
    const nativeAddToCartProductId =
      data.get("add-to-cart") || submitterProductId;

    // Keep add-to-cart in the native form fallback, but never send it to the
    // custom AJAX endpoint. WooCommerce's default form handler also watches
    // this field and can add the product before wc-ajax dispatches our handler.
    data.delete("add-to-cart");

    if (!data.get("product_id") && form.dataset.productId) {
      data.append("product_id", form.dataset.productId);
    } else if (!data.get("product_id") && nativeAddToCartProductId) {
      data.append("product_id", nativeAddToCartProductId);
    }

    variationAttributeInputs.forEach(function (input) {
      if (!input.name || !input.value) return;

      data.append("variation[" + input.name + "]", input.value);
    });

    const ajaxUrl = wc_cart_fragments_params.wc_ajax_url
      .toString()
      .replace("%%endpoint%%", "nh_add_to_cart");

    fetch(ajaxUrl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: data.toString(),
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (response) {
        if (response && (response.error || response.success === false)) {
          nativeSubmit(form);
          return;
        }

        jQuery(document.body).trigger("added_to_cart", [
          response.fragments,
          response.cart_hash,
        ]);

        if (response.fragments) {
          jQuery.each(response.fragments, function (key, value) {
            jQuery(key).replaceWith(value);
          });
        }
      })
      .catch(function () {
        nativeSubmit(form);
      })
      .finally(function () {
        addButtons.forEach(function (button) {
          button.classList.remove("is-loading");
        });

        if (variationOptions.length > 0) {
          const activeOption = singleProduct.querySelector(
            "[data-nh-variation-option].is-active",
          );
          setAddButtonsAvailability(
            !!activeOption && activeOption.dataset.purchasable === "1",
          );
        } else if (productFormOptions.length > 0) {
          const activeOption = singleProduct.querySelector(
            "[data-nh-product-form-option].is-active",
          );
          setAddButtonsAvailability(
            !!activeOption && activeOption.dataset.available === "1",
          );
        } else if (customConfigNode) {
          setAddButtonsAvailability(
            !priceBox?.classList.contains("nh-single-product__price-request"),
          );
        } else {
          addButtons.forEach(function (button) {
            button.disabled = false;
          });
        }
      });
  });
})();
