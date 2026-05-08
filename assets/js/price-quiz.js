// Price Quiz state machine — vanilla JS, one instance per block root.
//
// Root element carries the REST endpoint URL, nonce and privacy URL via
// data-attributes (set by the PHP render template). No globals, no
// wp_localize_script. Each step is rendered upfront; switching is done
// by updating data-nh-price-quiz-step on the root, which CSS attribute
// selectors use to show the active step.
//
// State kept in memory only (short quiz, no localStorage needed).

const SELECTOR = "[data-nh-price-quiz]";
const MAX_FILES = 3;
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB
const ALLOWED_MIME = ["image/jpeg", "image/png", "image/webp"];

class PriceQuiz {
  constructor(root) {
    this.root = root;
    this.endpoint = root.dataset.nhPriceQuizEndpoint;
    this.nonce = root.dataset.nhPriceQuizNonce;

    this.currentStep = 1;
    this.totalSteps = 4;
    this.isContact = false;
    this.isSubmitted = false;

    this.answers = {
      goal: [],
      length: "",
      current_ext: "",
      photos_mode: "",
    };
    this.files = [];
    this.contact = { name: "", whatsapp: "" };

    // Cache elements.
    this.stepperEl = root.querySelector("[data-nh-pq-stepper]");
    this.backEl = root.querySelector("[data-nh-pq-back]");
    this.nextEl = root.querySelector("[data-nh-pq-next]");
    this.nextLabelEl = this.nextEl?.querySelector(".nh-salon-price-quiz__next-label") ?? this.nextEl;
    this.hintEl = root.querySelector("[data-nh-pq-hint]");
    this.disclaimerEl = root.querySelector("[data-nh-pq-disclaimer]");
    this.fileInputEl = root.querySelector("[data-nh-pq-file-input]");
    this.badgeEl = root.querySelector("[data-nh-pq-badge]");
    this.successMsgEl = root.querySelector("[data-nh-pq-success-message]");
    this.whatsappBtnEl = root.querySelector("[data-nh-pq-whatsapp-btn]");
    this.honeypotEl = root.querySelector("[data-nh-pq-honeypot]");

    this.bindEvents();
    this.render();
  }

  bindEvents() {
    // Next button
    this.nextEl?.addEventListener("click", () => this.goNext());

    // Back button
    this.backEl?.addEventListener("click", () => this.goBack());

    // Checkbox / radio / text inputs — delegated change handler.
    this.root.addEventListener("change", (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;

      if (target.name === "goal") {
        this.answers.goal = Array.from(
          this.root.querySelectorAll('input[name="goal"]:checked')
        ).map((el) => el.value);
      } else if (target.name === "length") {
        this.answers.length = target.value;
      } else if (target.name === "current_ext") {
        this.answers.current_ext = target.value;
      } else if (target.name === "nh_pq_name") {
        this.contact.name = target.value;
      } else if (target.name === "nh_pq_whatsapp") {
        this.contact.whatsapp = target.value;
      }

      this.clearHint();
    });

    // Also clear hint on text input while typing.
    this.root.addEventListener("input", (event) => {
      const target = event.target;
      if (!(target instanceof HTMLInputElement)) return;
      if (target.name === "nh_pq_name") {
        this.contact.name = target.value;
        this.clearHint();
      } else if (target.name === "nh_pq_whatsapp") {
        this.contact.whatsapp = target.value;
        this.clearHint();
      }
    });

    // UPLOAD PHOTOS button → trigger hidden file input.
    const uploadBtn = this.root.querySelector("[data-nh-pq-upload]");
    uploadBtn?.addEventListener("click", () => {
      this.fileInputEl?.click();
    });

    // NO PHOTOS button → mark skipped path.
    const noPhotosBtn = this.root.querySelector("[data-nh-pq-no-photos]");
    noPhotosBtn?.addEventListener("click", () => {
      this.answers.photos_mode = "skipped";
      this.files = [];
      if (this.fileInputEl) this.fileInputEl.value = "";
      this.root.setAttribute("data-nh-pq-photos", "skipped");
      this.updateBadge();
      this.clearHint();
    });

    // File input change → validate and store.
    this.fileInputEl?.addEventListener("change", (event) => {
      this.handleFileSelect(event.target);
    });
  }

  goNext() {
    if (this.isSubmitted) return;

    const result = this.validateCurrent();
    if (!result.valid) {
      this.showHint(result.hint);
      return;
    }
    this.clearHint();

    if (this.isContact) {
      this.submit();
      return;
    }

    if (this.currentStep < this.totalSteps) {
      this.currentStep += 1;
    } else {
      this.isContact = true;
    }
    this.render();
  }

  goBack() {
    if (this.isSubmitted) return;

    if (this.isContact) {
      this.isContact = false;
    } else if (this.currentStep > 1) {
      this.currentStep -= 1;
    }
    this.clearHint();
    this.render();
  }

  validateCurrent() {
    if (this.isContact) {
      if (!this.contact.name.trim()) {
        return { valid: false, hint: "Please enter your name." };
      }
      const digits = this.contact.whatsapp.replace(/\D+/g, "");
      if (digits.length < 7 || digits.length > 15) {
        return {
          valid: false,
          hint: "Please enter a valid WhatsApp number (7–15 digits).",
        };
      }
      return { valid: true };
    }

    switch (this.currentStep) {
      case 1:
        if (this.answers.goal.length === 0) {
          return {
            valid: false,
            hint: "Please select at least one option.",
          };
        }
        break;
      case 2:
        if (!this.answers.length) {
          return { valid: false, hint: "Please pick a length." };
        }
        break;
      case 3:
        if (!this.answers.current_ext) {
          return { valid: false, hint: "Please answer Yes or No." };
        }
        break;
      case 4:
        if (!this.answers.photos_mode) {
          return {
            valid: false,
            hint: "Upload photos or pick “No photos” to continue.",
          };
        }
        break;
    }
    return { valid: true };
  }

  handleFileSelect(input) {
    if (!input || !input.files) return;
    const rawFiles = Array.from(input.files);
    const errors = [];
    const accepted = [];

    rawFiles.slice(0, MAX_FILES).forEach((file) => {
      if (!ALLOWED_MIME.includes(file.type)) {
        errors.push(`${file.name}: unsupported file type`);
        return;
      }
      if (file.size > MAX_FILE_SIZE) {
        errors.push(`${file.name}: larger than 10 MB`);
        return;
      }
      accepted.push(file);
    });

    if (rawFiles.length > MAX_FILES) {
      errors.push(`Only the first ${MAX_FILES} photos are kept.`);
    }

    this.files = accepted;
    this.answers.photos_mode = accepted.length > 0 ? "uploaded" : "";
    this.root.setAttribute(
      "data-nh-pq-photos",
      accepted.length > 0 ? "uploaded" : ""
    );
    this.updateBadge();

    if (errors.length) {
      this.showHint(errors.join("\n"));
    } else {
      this.clearHint();
    }
  }

  updateBadge() {
    if (!this.badgeEl) return;
    const count = this.files.length;
    if (count > 0) {
      this.badgeEl.textContent = String(count);
      this.badgeEl.hidden = false;
    } else {
      this.badgeEl.textContent = "0";
      this.badgeEl.hidden = true;
    }
  }

  render() {
    const stepAttr = this.isContact ? "contact" : String(this.currentStep);
    this.root.setAttribute("data-nh-price-quiz-step", stepAttr);

    // Stepper text.
    if (this.stepperEl) {
      this.stepperEl.textContent = this.isContact
        ? ""
        : `Step ${this.currentStep}. / ${this.totalSteps}`;
    }

    // Back button visibility: hidden on step 1 only.
    if (this.backEl) {
      const shouldShow = this.currentStep > 1 || this.isContact;
      this.backEl.hidden = !shouldShow;
    }

    // Next button label.
    if (this.nextLabelEl) {
      this.nextLabelEl.textContent = this.isContact ? "CHECK PRICE" : "NEXT";
    }

    // Disclaimer shown only on contact screen.
    if (this.disclaimerEl) {
      this.disclaimerEl.hidden = !this.isContact;
    }
  }

  showHint(message) {
    if (!this.hintEl) return;
    this.hintEl.textContent = message;
    this.hintEl.hidden = false;
  }

  clearHint() {
    if (!this.hintEl) return;
    this.hintEl.textContent = "";
    this.hintEl.hidden = true;
  }

  getValidationMessage(field) {
    switch (field) {
      case "goal":
        return "Please select at least one option.";
      case "length":
        return "Please pick a length.";
      case "current_ext":
        return "Please answer Yes or No.";
      case "photos_mode":
      case "photos":
        return "Upload photos or choose “No photos” to continue.";
      case "name":
        return "Please enter your name.";
      case "whatsapp":
        return "Please enter a valid WhatsApp number (7–15 digits).";
      default:
        return "Please check the form and try again.";
    }
  }

  getSubmitErrorMessage(json, response) {
    if (!json || typeof json !== "object") {
      if (response?.status === 429) {
        return "Too many attempts. Please wait a little and try again.";
      }
      return "Something went wrong. Please try again.";
    }

    switch (json.error) {
      case "validation":
        return this.getValidationMessage(json.field);
      case "invalid_nonce":
        return "Your session expired. Please refresh the page and try again.";
      case "rate_limited":
        return "Too many attempts. Please wait a little and try again.";
      case "file_invalid":
        return "Please upload JPG, PNG or WebP images up to 10 MB each.";
      case "upload_failed":
      case "insert_failed":
        return "We could not submit your request right now. Please try again in a moment.";
      default:
        return "Something went wrong. Please try again.";
    }
  }

  async submit() {
    if (!this.endpoint) {
      this.showHint("Submission endpoint is not configured.");
      return;
    }

    if (this.nextEl) {
      this.nextEl.disabled = true;
      if (this.nextLabelEl) this.nextLabelEl.textContent = "Submitting…";
    }

    const fd = new FormData();
    fd.append("goal", JSON.stringify(this.answers.goal));
    fd.append("length", this.answers.length);
    fd.append("current_ext", this.answers.current_ext);
    fd.append("photos_mode", this.answers.photos_mode);
    fd.append("name", this.contact.name);
    fd.append("whatsapp", this.contact.whatsapp);
    fd.append("hp_field", this.honeypotEl?.value ?? "");

    this.files.forEach((file, index) => {
      fd.append(`photos[${index}]`, file, file.name);
    });

    try {
      const res = await fetch(this.endpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: { "X-WP-Nonce": this.nonce },
        body: fd,
      });

      let json = null;
      try {
        json = await res.json();
      } catch (e) {
        /* noop */
      }

      if (!res.ok || !json || json.success !== true) {
        throw new Error(this.getSubmitErrorMessage(json, res));
      }

      this.showSuccess(json);
    } catch (err) {
      if (this.nextEl) {
        this.nextEl.disabled = false;
        if (this.nextLabelEl) this.nextLabelEl.textContent = "CHECK PRICE";
      }
      this.showHint(
        (err && err.message) || "Something went wrong. Please try again."
      );
    }
  }

  showSuccess({ message, whatsapp }) {
    this.isSubmitted = true;
    this.root.classList.add("nh-salon-price-quiz--submitted");

    if (this.successMsgEl && message) {
      this.successMsgEl.innerHTML = String(message).replace(/\n/g, "<br>");
    }

    if (this.whatsappBtnEl) {
      if (whatsapp) {
        this.whatsappBtnEl.href = `https://wa.me/${encodeURIComponent(whatsapp)}`;
        this.whatsappBtnEl.hidden = false;
      } else {
        this.whatsappBtnEl.hidden = true;
      }
    }
  }
}

function bootAll() {
  document.querySelectorAll(SELECTOR).forEach((root) => {
    if (root.dataset.nhPriceQuizInit === "1") return;
    root.dataset.nhPriceQuizInit = "1";
    new PriceQuiz(root);
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", bootAll);
} else {
  bootAll();
}
