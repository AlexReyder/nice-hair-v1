import Swiper from "swiper";
import { Navigation } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";

const SELECTOR = "[data-nh-results-swiper]";

function initResultsSlider(root) {
  const scope = root.closest(".nh-salon-results") || document;
  const prevEl = scope.querySelector("[data-nh-results-prev]");
  const nextEl = scope.querySelector("[data-nh-results-next]");
  const slideCount = root.querySelectorAll(".swiper-slide").length;

  // Swiper loop requires strictly more slides than the largest
  // slidesPerView. At desktop the view is 4 cards, so loop is only
  // safe with ≥5 slides — otherwise Swiper throws "Not enough slides
  // for loop mode" and falls back in a buggy way. Client starts with
  // 4 seed cards, so loop starts disabled and auto-enables as soon as
  // they add a 5th card.
  const canLoop = slideCount >= 5;

  return new Swiper(root, {
    modules: [Navigation],
    slidesPerView: 1.05,
    spaceBetween: 12,
    loop: canLoop,
    watchOverflow: false,
    watchSlidesProgress: true,
    navigation: {
      prevEl,
      nextEl,
      disabledClass: "swiper-button-disabled",
    },
    breakpoints: {
      640:  { slidesPerView: 1.6, spaceBetween: 14 },
      768:  { slidesPerView: 2.3, spaceBetween: 16 },
      1024: { slidesPerView: 3,   spaceBetween: 18 },
      1280: { slidesPerView: 4,   spaceBetween: 18 },
    },
  });
}

function bootAll() {
  document.querySelectorAll(SELECTOR).forEach((root) => {
    if (root.dataset.nhResultsInit === "1") return;
    root.dataset.nhResultsInit = "1";
    initResultsSlider(root);
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", bootAll);
} else {
  bootAll();
}
