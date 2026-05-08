const RUNNING_LINE_SELECTOR = "[data-nh-running-line]";
const MIN_SEQUENCE_COPIES = 3;
const MIN_WIDTH_MULTIPLIER = 1.5;
const MAX_SEQUENCE_COPIES = 20;
const PIXELS_PER_SECOND = 90;

const disableSequenceFocus = (sequence) => {
  sequence.setAttribute("aria-hidden", "true");

  sequence
    .querySelectorAll("a, button, input, select, textarea, [tabindex]")
    .forEach((element) => {
      element.setAttribute("tabindex", "-1");
    });
};

const buildRunningLine = (line) => {
  const viewport = line.querySelector("[data-nh-running-line-viewport]");
  const source = line.querySelector("[data-nh-running-line-source]");
  const copy = line.querySelector("[data-nh-running-line-copy]");
  const template = source?.querySelector("[data-nh-running-line-sequence]");

  if (!viewport || !source || !copy || !template) {
    return;
  }

  line.classList.remove("is-ready");

  const templateClone = template.cloneNode(true);
  source.innerHTML = "";
  copy.innerHTML = "";

  let copies = 0;
  const minWidth = viewport.offsetWidth * MIN_WIDTH_MULTIPLIER;

  while (
    copies < MIN_SEQUENCE_COPIES ||
    (source.scrollWidth < minWidth && copies < MAX_SEQUENCE_COPIES)
  ) {
    const sequence = templateClone.cloneNode(true);

    if (copies > 0) {
      disableSequenceFocus(sequence);
    }

    source.appendChild(sequence);
    copies += 1;
  }

  copy.innerHTML = source.innerHTML;
  copy
    .querySelectorAll("[data-nh-running-line-sequence]")
    .forEach(disableSequenceFocus);

  const duration = Math.max(source.scrollWidth / PIXELS_PER_SECOND, 18);
  line.style.setProperty("--nh-running-line-duration", `${duration}s`);
  line.classList.add("is-ready");
};

const initRunningLine = (line) => {
  const viewport = line.querySelector("[data-nh-running-line-viewport]");

  if (!viewport) {
    return;
  }

  let frame = 0;
  const scheduleBuild = () => {
    window.cancelAnimationFrame(frame);
    frame = window.requestAnimationFrame(() => buildRunningLine(line));
  };

  scheduleBuild();
  window.addEventListener("load", scheduleBuild, { once: true });

  if ("ResizeObserver" in window) {
    const observer = new ResizeObserver(scheduleBuild);
    observer.observe(viewport);
    return;
  }

  window.addEventListener("resize", scheduleBuild);
};

document.querySelectorAll(RUNNING_LINE_SELECTOR).forEach(initRunningLine);
