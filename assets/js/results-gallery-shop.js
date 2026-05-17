import Swiper from "swiper";
import { Navigation } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";

const SELECTOR = "[data-nh-results-gallery-shop-swiper]";
const GALLERY_SELECTOR = "[data-nh-results-gallery-shop]";
const GALLERY_ITEM_SELECTOR = "[data-nh-results-gallery-shop-item]";

function initResultsGalleryShopSlider(root) {
  const scope = root.closest(".nh-results-gallery-shop") || document;
  const prevEl = scope.querySelector("[data-nh-results-gallery-shop-prev]");
  const nextEl = scope.querySelector("[data-nh-results-gallery-shop-next]");
  const slideCount = root.querySelectorAll(".swiper-slide").length;
  const canLoop = slideCount >= 5;
  const navigation = prevEl && nextEl
    ? {
        prevEl,
        nextEl,
        disabledClass: "swiper-button-disabled",
      }
    : false;

  return new Swiper(root, {
    modules: [Navigation],
    slidesPerView: 1.05,
    spaceBetween: 12,
    loop: canLoop,
    watchOverflow: false,
    watchSlidesProgress: true,
    navigation,
    breakpoints: {
      640: { slidesPerView: 1.6, spaceBetween: 14 },
      768: { slidesPerView: 2.3, spaceBetween: 16 },
      1024: { slidesPerView: 3, spaceBetween: 18 },
      1280: { slidesPerView: 4, spaceBetween: 18 },
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
      const title = trigger.getAttribute("data-nh-results-gallery-shop-title") || "";

      return {
        src: trigger.href,
        w: getPositiveInteger(
          trigger.getAttribute("data-nh-results-gallery-shop-width"),
          1200
        ),
        h: getPositiveInteger(
          trigger.getAttribute("data-nh-results-gallery-shop-height"),
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

function initResultsGalleryShopPhotoSwipe() {
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
    if (root.dataset.nhResultsGalleryShopInit === "1") return;
    root.dataset.nhResultsGalleryShopInit = "1";
    initResultsGalleryShopSlider(root);
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", bootAll);
} else {
  bootAll();
}

initResultsGalleryShopPhotoSwipe();
