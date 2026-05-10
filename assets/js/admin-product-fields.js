(() => {
  const config = window.nhProductAdminFields || {};
  const defaultFamily = config.defaultFamily || "default";
  const currentFamily = normalizeFamily(config.currentFamily);
  const familyByTermId = config.familyByTermId || {};
  const familyPriority = Array.isArray(config.familyPriority)
    ? config.familyPriority
    : [];
  const fieldGroups = config.fieldGroups || {};

  const categorySelector = [
    '#product_catchecklist input[type="checkbox"][value]',
    '#product_catchecklist-pop input[type="checkbox"][value]',
    'input[type="checkbox"][name="tax_input[product_cat][]"][value]',
  ].join(",");

  const knownFamilies = new Set([defaultFamily]);

  function normalizeFamily(family) {
    return typeof family === "string" && family !== "" ? family : defaultFamily;
  }

  function addKnownFamily(family) {
    if (typeof family !== "string" || family === "" || family === "*") {
      return;
    }

    knownFamilies.add(family);
  }

  familyPriority.forEach(addKnownFamily);

  Object.values(fieldGroups).forEach((families) => {
    if (!Array.isArray(families)) {
      return;
    }

    families.forEach(addKnownFamily);
  });

  function selectedFamilies() {
    const inputs = Array.from(document.querySelectorAll(categorySelector));
    const families = new Set();

    inputs.forEach((input) => {
      if (!(input instanceof HTMLInputElement) || !input.checked) {
        return;
      }

      const family = familyByTermId[String(input.value)];

      if (family) {
        families.add(family);
      }
    });

    return { families, hasCategoryInputs: inputs.length > 0 };
  }

  function resolveFamily() {
    const { families, hasCategoryInputs } = selectedFamilies();

    for (const family of familyPriority) {
      if (families.has(family)) {
        return family;
      }
    }

    if (!hasCategoryInputs) {
      return currentFamily;
    }

    return defaultFamily;
  }

  function setBodyFamily(family) {
    const body = document.body;
    const nextClass = `nh-product-family--${family}`;

    knownFamilies.forEach((knownFamily) => {
      body.classList.remove(`nh-product-family--${knownFamily}`);
    });

    body.classList.add("nh-product-admin-fields", nextClass);
  }

  function escapeAttributeValue(value) {
    return String(value).replace(/\\/g, "\\\\").replace(/"/g, '\\"');
  }

  function fieldGroupElements(groupKey) {
    const safeGroupKey = escapeAttributeValue(groupKey);

    return Array.from(
      document.querySelectorAll(
        `.acf-postbox[data-key="${safeGroupKey}"], #acf-${groupKey}`,
      ),
    ).filter((group) => group instanceof HTMLElement);
  }

  function setFieldGroupVisible(groupKey, visible) {
    const groups = fieldGroupElements(groupKey);

    if (!groups.length) {
      return;
    }

    groups.forEach((group) => {
      group.style.setProperty(
        "display",
        visible ? "block" : "none",
        "important",
      );
      group.setAttribute("aria-hidden", visible ? "false" : "true");
    });
  }

  function applyVisibility() {
    const family = resolveFamily();

    setBodyFamily(family);

    Object.entries(fieldGroups).forEach(([groupKey, allowedFamilies]) => {
      const visible =
        Array.isArray(allowedFamilies) &&
        (allowedFamilies.includes(family) || allowedFamilies.includes("*"));

      setFieldGroupVisible(groupKey, visible);
    });
  }

  function bindCategoryChanges() {
    document.addEventListener("change", (event) => {
      const target = event.target;

      if (
        target instanceof HTMLInputElement &&
        target.matches(categorySelector)
      ) {
        window.requestAnimationFrame(applyVisibility);
      }
    });
  }

  bindCategoryChanges();

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", applyVisibility);
  } else {
    applyVisibility();
  }

  window.addEventListener("load", applyVisibility, { once: true });

  if (window.acf && typeof window.acf.addAction === "function") {
    window.acf.addAction("ready", applyVisibility);
    window.acf.addAction("append", applyVisibility);
  }
})();
