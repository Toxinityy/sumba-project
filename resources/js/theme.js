const KEY = "hfs-theme";
const darkQuery = window.matchMedia("(prefers-color-scheme: dark)");

/* The saved choice: "light", "dark", or "system" (nothing chosen yet). */
function saved() {
  try {
    return localStorage.getItem(KEY) || "system";
  } catch (e) {
    return "system";
  }
}

/* The theme actually on screen, resolving "system" against the device. */
function effective(choice) {
  if (choice === "light" || choice === "dark") return choice;
  return darkQuery.matches ? "dark" : "light";
}

function apply(choice) {
  // "system" means NO attribute, so prefers-color-scheme governs.
  if (choice === "light" || choice === "dark") {
    document.documentElement.setAttribute("data-theme", choice);
  } else {
    document.documentElement.removeAttribute("data-theme");
  }

  // aria-pressed="true" means dark mode is on.
  const isDark = effective(choice) === "dark";
  document.querySelectorAll("[data-theme-toggle]").forEach((btn) => {
    btn.setAttribute("aria-pressed", isDark ? "true" : "false");
  });
}

export function initTheme() {
  apply(saved());

  // Clicking switches to the opposite of what is on screen, and from then on
  // the choice is explicit rather than following the device.
  document.addEventListener("click", (event) => {
    if (!event.target.closest("[data-theme-toggle]")) return;

    const next = effective(saved()) === "dark" ? "light" : "dark";
    try {
      localStorage.setItem(KEY, next);
    } catch (e) {
      /* private mode: the preference just doesn't persist */
    }
    apply(next);
  });

  // While following the device, keep the button's state honest if the
  // device switches between light and dark (e.g. automatically at sunset).
  darkQuery.addEventListener("change", () => {
    if (saved() === "system") apply("system");
  });
}
