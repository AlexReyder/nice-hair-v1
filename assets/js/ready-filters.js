const FILTER_FORM_SELECTOR = `[data-nh-ready-filters-form]`;
const FILTER_INPUT_SELECTOR = `[data-nh-ready-filter-input]`;
const FILTER_OPTION_SELECTOR = `[data-nh-ready-filter-option]`;
const FILTER_GROUP_SELECTOR = `[data-nh-ready-filter-group]`;
const MANUAL_SUBMIT_MODE = `manual`;

const getFilterSubmitMode = (form) =>
  form.dataset.nhReadyFiltersSubmitMode === MANUAL_SUBMIT_MODE ? MANUAL_SUBMIT_MODE : `auto`;

const syncFilterOptionState = (form) => {
  const inputs = Array.from(form.querySelectorAll(FILTER_INPUT_SELECTOR));

  inputs.forEach((input) => {
    const option = input.closest(FILTER_OPTION_SELECTOR);

    if (!option) {
      return;
    }

    option.classList.toggle(`is-active`, input.checked);
  });
};

const syncFilterGroupState = (form) => {
  const groups = Array.from(form.querySelectorAll(FILTER_GROUP_SELECTOR));

  groups.forEach((group) => {
    const inputs = Array.from(group.querySelectorAll(FILTER_INPUT_SELECTOR));
    const activeCount = inputs.filter((input) => input.checked).length;

    group.classList.toggle(`is-active`, activeCount > 0);
  });
};

const submitReadyFilters = (form) => {
  if (form.dataset.isSubmitting === `true`) {
    return;
  }

  form.dataset.isSubmitting = `true`;

  if (typeof form.requestSubmit === `function`) {
    form.requestSubmit();
    return;
  }

  form.submit();
};

const initReadyFilters = () => {
  const forms = Array.from(document.querySelectorAll(FILTER_FORM_SELECTOR));

  if (!forms.length) {
    return;
  }

  forms.forEach((form) => {
    syncFilterOptionState(form);
    syncFilterGroupState(form);

    form.addEventListener(`change`, (event) => {
      const target = event.target;

      if (!(target instanceof HTMLInputElement) || !target.matches(FILTER_INPUT_SELECTOR)) {
        return;
      }

      syncFilterOptionState(form);
      syncFilterGroupState(form);

      if (getFilterSubmitMode(form) !== MANUAL_SUBMIT_MODE) {
        submitReadyFilters(form);
      }
    });

    form.addEventListener(`submit`, () => {
      form.dataset.isSubmitting = `true`;
    });
  });
};

if (document.readyState === `loading`) {
  document.addEventListener(`DOMContentLoaded`, initReadyFilters);
} else {
  initReadyFilters();
}
