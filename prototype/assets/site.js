/* ==========================================================================
   Hope for Sumba — prototype behaviour

   Two controls, only one of which is throwaway:

   - Theme (ships). Light is "Open Field", dark is "Dusk Savanna" — one system
     at two luminances. Three states, not two: "system" removes the attribute
     entirely and lets prefers-color-scheme decide, which is what most visitors
     will get.

   - Language (prototype only). The real site uses locale-prefixed,
     server-rendered routes (/id/..., /en/...), each independently indexable.
     A client-side swap would be wrong in production. It exists here so the
     team can see the Indonesian-runs-longer problem in the actual layout
     rather than in a footnote.
   ========================================================================== */

(function () {
  "use strict";

  var LANG_KEY = "hfs-proto-lang";
  var THEME_KEY = "hfs-theme";
  var DEFAULT_LANG = "id";
  var DEFAULT_THEME = "system";

  /* localStorage throws in some privacy modes. Never let that break the page. */
  function read(key, fallback) {
    try {
      return window.localStorage.getItem(key) || fallback;
    } catch (e) {
      return fallback;
    }
  }

  function write(key, value) {
    try {
      window.localStorage.setItem(key, value);
    } catch (e) {
      /* preference simply doesn't persist — the page still works */
    }
  }

  function setPressed(selector, attr, value) {
    var buttons = document.querySelectorAll(selector);
    for (var i = 0; i < buttons.length; i++) {
      buttons[i].setAttribute(
        "aria-pressed",
        buttons[i].getAttribute(attr) === value ? "true" : "false"
      );
    }
  }

  /* ---- Language ------------------------------------------------------- */

  function applyLanguage(lang) {
    var nodes = document.querySelectorAll("[data-id]");
    for (var i = 0; i < nodes.length; i++) {
      var node = nodes[i];
      var next = lang === "en" ? node.getAttribute("data-en") : node.getAttribute("data-id");
      if (next !== null) {
        node.textContent = next;
      }
    }

    document.documentElement.setAttribute("lang", lang);
    setPressed("[data-lang-btn]", "data-lang-btn", lang);
    write(LANG_KEY, lang);
  }

  /* ---- Theme ---------------------------------------------------------- */

  function applyTheme(theme) {
    /* "system" means no attribute at all, so prefers-color-scheme governs.
       An explicit choice stamps the attribute and wins in both directions. */
    if (theme === "light" || theme === "dark") {
      document.documentElement.setAttribute("data-theme", theme);
    } else {
      document.documentElement.removeAttribute("data-theme");
    }

    setPressed("[data-theme-btn]", "data-theme-btn", theme);
    write(THEME_KEY, theme);
  }

  /* ---- Wiring --------------------------------------------------------- */

  document.addEventListener("click", function (event) {
    var langBtn = event.target.closest("[data-lang-btn]");
    if (langBtn) {
      applyLanguage(langBtn.getAttribute("data-lang-btn"));
      return;
    }

    var themeBtn = event.target.closest("[data-theme-btn]");
    if (themeBtn) {
      applyTheme(themeBtn.getAttribute("data-theme-btn"));
    }
  });

  function init() {
    applyTheme(read(THEME_KEY, DEFAULT_THEME));
    applyLanguage(read(LANG_KEY, DEFAULT_LANG));
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
