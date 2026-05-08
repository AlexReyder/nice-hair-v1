import Swiper from "swiper";
import { Navigation } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";

const SELECTOR = "[data-nh-shop-assortment-swiper]";

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
