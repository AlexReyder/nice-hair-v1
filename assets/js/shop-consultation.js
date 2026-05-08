(function () {
  const ROOT_SELECTOR = "[data-nh-shop-consultation]";
  const FORM_SELECTOR = "[data-nh-shop-consultation-form]";

  const roots = Array.from(document.querySelectorAll(ROOT_SELECTOR));

  if (!roots.length) {
    return;
  }

  const digits = (value) => String(value || "").replace(/\D+/g, "");

  const validationMessage = (field) => {
    switch (field) {
      case "name":
        return "Please enter your name.";
      case "phone":
        return "Please enter a valid phone number (7-15 digits).";
      case "whatsapp":
        return "Please enter a valid WhatsApp number (7-15 digits).";
      default:
        return "Please check the form and try again.";
    }
  };

  const responseErrorMessage = (errorCode, field = "") => {
    switch (errorCode) {
      case "validation":
        return validationMessage(field);
      case "rate_limited":
        return "Too many attempts. Please try again a little later.";
      case "invalid_nonce":
      case "insert_failed":
        return "Something went wrong. Please try again.";
      default:
        return errorCode || "Something went wrong. Please try again.";
    }
  };

  roots.forEach((root) => {
    const form = root.querySelector(FORM_SELECTOR);
    const hint = root.querySelector("[data-nh-shop-consultation-hint]");
    const success = root.querySelector("[data-nh-shop-consultation-success]");
    const successMessage = root.querySelector(
      "[data-nh-shop-consultation-success-message]"
    );
    const submitButton = root.querySelector("[data-nh-shop-consultation-submit]");
    const submitLabel = root.querySelector(
      ".nh-single-product__consultation-submit-label"
    );
    const pageUrl = root.querySelector("[data-nh-shop-consultation-page-url]");
    const defaultSubmitText =
      submitLabel instanceof HTMLElement
        ? submitLabel.textContent || "GET A CONSULTATION"
        : "GET A CONSULTATION";

    if (!(form instanceof HTMLFormElement)) {
      return;
    }

    const setHint = (message = "") => {
      if (!(hint instanceof HTMLElement)) {
        return;
      }

      hint.textContent = message;
      hint.hidden = message === "";
    };

    const setSubmitting = (isSubmitting) => {
      if (submitButton instanceof HTMLButtonElement) {
        submitButton.disabled = isSubmitting;
        submitButton.classList.toggle("is-loading", isSubmitting);
      }

      if (submitLabel instanceof HTMLElement) {
        submitLabel.textContent = isSubmitting ? "SENDING..." : defaultSubmitText;
      }
    };

    const reset = () => {
      form.reset();
      form.hidden = false;
      success?.setAttribute("hidden", "");
      setSubmitting(false);
      setHint("");

      if (pageUrl instanceof HTMLInputElement) {
        pageUrl.value = window.location.href;
      }
    };

    const validate = () => {
      const nameField = form.elements.namedItem("name");
      const phoneField = form.elements.namedItem("phone");
      const whatsappField = form.elements.namedItem("whatsapp");

      const name =
        nameField instanceof HTMLInputElement ? nameField.value.trim() : "";
      const phone =
        phoneField instanceof HTMLInputElement ? digits(phoneField.value) : "";
      const whatsapp =
        whatsappField instanceof HTMLInputElement
          ? digits(whatsappField.value)
          : "";

      if (!name) {
        return { valid: false, field: "name" };
      }

      if (phone.length < 7 || phone.length > 15) {
        return { valid: false, field: "phone" };
      }

      if (whatsapp.length < 7 || whatsapp.length > 15) {
        return { valid: false, field: "whatsapp" };
      }

      return { valid: true, field: "" };
    };

    const showSuccess = (payload = {}) => {
      form.hidden = true;
      success?.removeAttribute("hidden");

      if (successMessage instanceof HTMLElement && payload.message) {
        successMessage.textContent = String(payload.message);
      }
    };

    const submit = async () => {
      const validation = validate();

      if (!validation.valid) {
        setHint(validationMessage(validation.field));
        return;
      }

      const endpoint = root.dataset.endpoint || "";
      const nonce = root.dataset.nonce || "";

      if (!endpoint) {
        setHint("Submission endpoint is not configured.");
        return;
      }

      setSubmitting(true);
      setHint("");

      try {
        const response = await fetch(endpoint, {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "X-WP-Nonce": nonce,
          },
          body: new FormData(form),
        });

        let payload = null;

        try {
          payload = await response.json();
        } catch (error) {
          payload = null;
        }

        if (!response.ok || !payload || payload.success !== true) {
          throw new Error(
            JSON.stringify({
              error: payload?.error || `Request failed (${response.status})`,
              field: payload?.field || "",
            })
          );
        }

        showSuccess(payload);
      } catch (error) {
        let parsed = null;

        try {
          parsed = JSON.parse(error?.message || "{}");
        } catch (parseError) {
          parsed = null;
        }

        setSubmitting(false);
        setHint(responseErrorMessage(parsed?.error || error?.message, parsed?.field || ""));
      }
    };

    form.addEventListener("submit", (event) => {
      event.preventDefault();
      submit();
    });

    form.addEventListener("input", () => setHint(""));

    document.addEventListener("nice-hair:content-drawer-opened", (event) => {
      const drawerId = event.detail?.drawerId || "";
      const rootId = root.dataset.nhContentDrawerId || root.id || "";

      if (drawerId === rootId) {
        reset();
      }
    });

    reset();
  });
})();
