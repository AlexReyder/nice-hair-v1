import Swiper from "swiper";
import { Navigation } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";

const SELECTOR = "[data-nh-shop-assortment-swiper]";
const GALLERY_SELECTOR = "[data-nh-shop-assortment-gallery]";
const GALLERY_ITEM_SELECTOR = "[data-nh-shop-assortment-gallery-item]";

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

function getPhotoSwipeElement() {
  return document.querySelector(".pswp");
}

function canUseWooCommercePhotoSwipe() {
  return (
    typeof window.PhotoSwipe === "function" &&
    typeof window.PhotoSwipeUI_Default === "function" &&
    getPhotoSwipeElement() instanceof HTMLElement
  );
}

function getPositiveInteger(value, fallback) {
  const number = Number.parseInt(value || "", 10);

  return Number.isFinite(number) && number > 0 ? number : fallback;
}

function collectPhotoSwipeItems(gallery) {
  return Array.from(gallery.querySelectorAll(GALLERY_ITEM_SELECTOR))
    .filter((trigger) => trigger instanceof HTMLAnchorElement)
    .map((trigger) => {
      const title = trigger.getAttribute("data-nh-shop-assortment-gallery-title") || "";

      return {
        src: trigger.href,
        w: getPositiveInteger(
          trigger.getAttribute("data-nh-shop-assortment-gallery-width"),
          1200
        ),
        h: getPositiveInteger(
          trigger.getAttribute("data-nh-shop-assortment-gallery-height"),
          900
        ),
        title,
        trigger,
      };
    })
    .filter((item) => item.src !== "");
}

function getThumbBounds(item) {
  const image = item?.trigger?.querySelector("img");

  if (!(image instanceof HTMLImageElement)) {
    return null;
  }

  const rect = image.getBoundingClientRect();
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

  return {
    x: rect.left,
    y: rect.top + scrollTop,
    w: rect.width,
  };
}

function openWooCommercePhotoSwipe(gallery, trigger) {
  const pswpElement = getPhotoSwipeElement();

  if (
    !(pswpElement instanceof HTMLElement) ||
    typeof window.PhotoSwipe !== "function" ||
    typeof window.PhotoSwipeUI_Default !== "function"
  ) {
    return false;
  }

  const items = collectPhotoSwipeItems(gallery);
  const index = Math.max(
    0,
    items.findIndex((item) => item.trigger === trigger)
  );

  if (!items.length) {
    return false;
  }

  const instance = new window.PhotoSwipe(
    pswpElement,
    window.PhotoSwipeUI_Default,
    items,
    {
      index,
      history: false,
      focus: true,
      bgOpacity: 0.86,
      showHideOpacity: true,
      closeOnScroll: false,
      shareEl: false,
      fullscreenEl: false,
      captionEl: false,
      arrowEl: items.length > 1,
      counterEl: items.length > 1,
      getThumbBoundsFn: (itemIndex) => getThumbBounds(items[itemIndex]),
    }
  );

  instance.init();

  return true;
}

function initGalleryPhotoSwipe() {
  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const trigger = event.target.closest(GALLERY_ITEM_SELECTOR);

    if (!(trigger instanceof HTMLAnchorElement)) {
      return;
    }

    const gallery = trigger.closest(GALLERY_SELECTOR);

    if (!(gallery instanceof HTMLElement)) {
      return;
    }

    const swiperRoot = trigger.closest(SELECTOR);
    const swiper = swiperRoot?.swiper;

    if (swiper && swiper.allowClick === false) {
      event.preventDefault();
      return;
    }

    if (!canUseWooCommercePhotoSwipe()) {
      return;
    }

    event.preventDefault();
    openWooCommercePhotoSwipe(gallery, trigger);
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

initGalleryPhotoSwipe();
