import Swiper from "swiper";
import { Navigation } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";

const SELECTOR = "[data-nh-stylists-swiper]";

function initStylistsSlider(root) {
  const scope = root.closest(".nh-salon-stylists") || document;
  const prevEl = scope.querySelector("[data-nh-stylists-prev]");
  const nextEl = scope.querySelector("[data-nh-stylists-next]");

  return new Swiper(root, {
    modules: [Navigation],
    slidesPerView: 1.05,
    spaceBetween: 12,
    loop: false,
    watchOverflow: false,
    navigation: {
      prevEl,
      nextEl,
      disabledClass: "swiper-button-disabled",
    },
    breakpoints: {
      640: { slidesPerView: 1.6, spaceBetween: 14 },
      768: { slidesPerView: 2.3, spaceBetween: 14 },
      1024: { slidesPerView: 3, spaceBetween: 14 },
      1280: { slidesPerView: 3, spaceBetween: 18 },
    },
  });
}

function bootAll() {
  document.querySelectorAll(SELECTOR).forEach((root) => {
    if (root.dataset.nhStylistsInit === "1") return;
    root.dataset.nhStylistsInit = "1";
    initStylistsSlider(root);
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", bootAll);
} else {
  bootAll();
}
