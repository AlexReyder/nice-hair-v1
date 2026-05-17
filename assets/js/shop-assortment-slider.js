import Swiper from "swiper";
import { Navigation } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";

const SELECTOR = "[data-nh-shop-assortment-swiper]";
const GALLERY_SELECTOR = "[data-nh-shop-assortment-gallery]";
const GALLERY_ITEM_SELECTOR = "[data-nh-shop-assortment-gallery-item]";
const LIGHTBOX_OPEN_CLASS = "is-open";
const BODY_LIGHTBOX_CLASS = "nh-shop-assortment-lightbox-open";
const TRANSITION_MS = 250;

let lightbox = null;
let lightboxState = {
  items: [],
  index: 0,
  closeTimer: null,
  previousActiveElement: null,
};

function initShopAssortmentSlider(root) {
  const scope = root.closest(".nh-shop-assortment") || document;
  const prevEl = scope.querySelector("[data-nh-shop-assortment-prev]");
  const nextEl = scope.querySelector("[data-nh-shop-assortment-next]");

  return new Swiper(root, {
    modules: [Navigation],
    slidesPerView: 1.18,
    spaceBetween: 14,
    loop: false,
    watchOverflow: false,
    watchSlidesProgress: true,
    navigation: {
      prevEl,
      nextEl,
      disabledClass: "swiper-button-disabled",
    },
    breakpoints: {
      640: { slidesPerView: 2.2, spaceBetween: 16 },
      768: { slidesPerView: 3.2, spaceBetween: 18 },
      1024: { slidesPerView: 4.2, spaceBetween: 18 },
      1280: { slidesPerView: 5.15, spaceBetween: 20 },
    },
  });
}

function getScrollbarCompensation() {
  return Math.max(0, window.innerWidth - document.documentElement.clientWidth);
}

function collectGalleryItems(gallery) {
  return Array.from(gallery.querySelectorAll(GALLERY_ITEM_SELECTOR))
    .map((item) => ({
      trigger: item,
      src: item.getAttribute("data-nh-shop-assortment-gallery-src") || "",
      alt: item.getAttribute("data-nh-shop-assortment-gallery-alt") || "",
    }))
    .filter((item) => item.src !== "");
}

function createLightbox() {
  if (lightbox) {
    return lightbox;
  }

  const root = document.createElement("div");
  root.className = "nh-shop-assortment-lightbox";
  root.hidden = true;
  root.setAttribute("data-nh-shop-assortment-lightbox", "");

  root.innerHTML = `
    <div class="nh-shop-assortment-lightbox__backdrop" data-nh-shop-assortment-lightbox-close></div>
    <div
      class="nh-shop-assortment-lightbox__dialog"
      role="dialog"
      aria-modal="true"
      aria-label="Assortment image preview"
    >
      <button
        class="nh-shop-assortment-lightbox__close"
        type="button"
        aria-label="Close image preview"
        data-nh-shop-assortment-lightbox-close
      >
        <span aria-hidden="true">×</span>
      </button>

      <button
        class="nh-shop-assortment-lightbox__nav nh-shop-assortment-lightbox__nav--prev"
        type="button"
        aria-label="Previous image"
        data-nh-shop-assortment-lightbox-prev
      >
        <span class="nh-shop-assortment-lightbox__nav-icon" aria-hidden="true"></span>
      </button>

      <figure class="nh-shop-assortment-lightbox__figure">
        <img class="nh-shop-assortment-lightbox__image" src="" alt="" data-nh-shop-assortment-lightbox-image>
      </figure>

      <button
        class="nh-shop-assortment-lightbox__nav nh-shop-assortment-lightbox__nav--next"
        type="button"
        aria-label="Next image"
        data-nh-shop-assortment-lightbox-next
      >
        <span class="nh-shop-assortment-lightbox__nav-icon" aria-hidden="true"></span>
      </button>
    </div>
  `;

  document.body.appendChild(root);

  lightbox = {
    root,
    image: root.querySelector("[data-nh-shop-assortment-lightbox-image]"),
    closeButtons: root.querySelectorAll("[data-nh-shop-assortment-lightbox-close]"),
    prevButton: root.querySelector("[data-nh-shop-assortment-lightbox-prev]"),
    nextButton: root.querySelector("[data-nh-shop-assortment-lightbox-next]"),
  };

  lightbox.closeButtons.forEach((button) => {
    button.addEventListener("click", closeLightbox);
  });

  lightbox.prevButton?.addEventListener("click", () => showLightboxItem(lightboxState.index - 1));
  lightbox.nextButton?.addEventListener("click", () => showLightboxItem(lightboxState.index + 1));

  document.addEventListener("keydown", handleLightboxKeydown);

  return lightbox;
}

function showLightboxItem(index) {
  const instance = createLightbox();
  const items = lightboxState.items;

  if (!items.length) {
    return;
  }

  const normalizedIndex = ((index % items.length) + items.length) % items.length;
  const item = items[normalizedIndex];

  lightboxState.index = normalizedIndex;

  if (instance.image instanceof HTMLImageElement) {
    instance.image.src = item.src;
    instance.image.alt = item.alt;
  }

  const hasMultipleItems = items.length > 1;

  [instance.prevButton, instance.nextButton].forEach((button) => {
    if (!(button instanceof HTMLButtonElement)) {
      return;
    }

    button.hidden = !hasMultipleItems;
    button.disabled = !hasMultipleItems;
  });
}

function openLightbox(gallery, trigger) {
  const instance = createLightbox();
  const items = collectGalleryItems(gallery);
  const index = Math.max(
    0,
    items.findIndex((item) => item.trigger === trigger)
  );

  if (!items.length) {
    return;
  }

  if (lightboxState.closeTimer) {
    window.clearTimeout(lightboxState.closeTimer);
    lightboxState.closeTimer = null;
  }

  lightboxState.items = items;
  lightboxState.previousActiveElement =
    trigger instanceof HTMLElement
      ? trigger
      : document.activeElement instanceof HTMLElement
        ? document.activeElement
        : null;

  showLightboxItem(index);

  document.documentElement.style.setProperty(
    "--nh-scrollbar-compensation",
    `${getScrollbarCompensation()}px`
  );
  document.body.classList.add(BODY_LIGHTBOX_CLASS);

  instance.root.hidden = false;
  void instance.root.offsetWidth;
  instance.root.classList.add(LIGHTBOX_OPEN_CLASS);

  window.requestAnimationFrame(() => {
    if (instance.closeButtons[0] instanceof HTMLElement) {
      instance.closeButtons[0].focus({ preventScroll: true });
    }
  });
}

function closeLightbox() {
  if (!lightbox) {
    return;
  }

  if (lightboxState.closeTimer) {
    window.clearTimeout(lightboxState.closeTimer);
    lightboxState.closeTimer = null;
  }

  lightbox.root.classList.remove(LIGHTBOX_OPEN_CLASS);
  document.body.classList.remove(BODY_LIGHTBOX_CLASS);
  document.documentElement.style.removeProperty("--nh-scrollbar-compensation");

  lightboxState.closeTimer = window.setTimeout(() => {
    if (lightbox) {
      lightbox.root.hidden = true;
    }

    lightboxState.closeTimer = null;
  }, TRANSITION_MS);

  if (
    lightboxState.previousActiveElement instanceof HTMLElement &&
    lightboxState.previousActiveElement.isConnected
  ) {
    lightboxState.previousActiveElement.focus({ preventScroll: true });
  }

  lightboxState.previousActiveElement = null;
}

function handleLightboxKeydown(event) {
  if (!lightbox || lightbox.root.hidden) {
    return;
  }

  if (event.key === "Escape") {
    event.preventDefault();
    closeLightbox();
    return;
  }

  if (event.key === "ArrowLeft") {
    event.preventDefault();
    showLightboxItem(lightboxState.index - 1);
    return;
  }

  if (event.key === "ArrowRight") {
    event.preventDefault();
    showLightboxItem(lightboxState.index + 1);
  }
}

function initGalleryLightbox() {
  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const trigger = event.target.closest(GALLERY_ITEM_SELECTOR);

    if (!(trigger instanceof HTMLElement)) {
      return;
    }

    const gallery = trigger.closest(GALLERY_SELECTOR);

    if (!(gallery instanceof HTMLElement)) {
      return;
    }

    const swiperRoot = trigger.closest(SELECTOR);
    const swiper = swiperRoot?.swiper;

    if (swiper && swiper.allowClick === false) {
      return;
    }

    event.preventDefault();
    openLightbox(gallery, trigger);
  });
}

function bootAll() {
  document.querySelectorAll(SELECTOR).forEach((root) => {
    if (root.dataset.nhShopAssortmentInit === "1") return;
    root.dataset.nhShopAssortmentInit = "1";
    initShopAssortmentSlider(root);
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", bootAll);
} else {
  bootAll();
}

initGalleryLightbox();
