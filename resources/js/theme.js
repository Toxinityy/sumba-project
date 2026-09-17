const KEY = "hfs-theme";

function apply(theme) {
  // "system" means NO attribute, so prefers-color-scheme governs.
  if (theme === "light" || theme === "dark") {
    document.documentElement.setAttribute("data-theme", theme);
  } else {
    document.documentElement.removeAttribute("data-theme");
  }

  document.querySelectorAll("[data-theme-btn]").forEach((btn) => {
    btn.setAttribute(
      "aria-pressed",
      btn.getAttribute("data-theme-btn") === theme ? "true" : "false"
    );
  });

  try {
    localStorage.setItem(KEY, theme);
  } catch (e) {
    /* private mode: the preference just doesn't persist */
  }
}

export function initTheme() {
  let saved = "system";
  try {
    saved = localStorage.getItem(KEY) || "system";
  } catch (e) {}

  apply(saved);

  document.addEventListener("click", (event) => {
    const btn = event.target.closest("[data-theme-btn]");
    if (btn) apply(btn.getAttribute("data-theme-btn"));
  });
}
