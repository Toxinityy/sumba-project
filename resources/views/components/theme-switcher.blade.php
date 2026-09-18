{{-- resources/views/components/theme-switcher.blade.php --}}
{{--
  One sun/moon button, placed in the nav beside the language switcher.

  Until someone clicks it, the page follows the device's own light/dark
  setting: no data-theme attribute is stamped, and prefers-color-scheme
  decides. That "system" state is what most visitors are in, so it stays the
  default even though there is no third button for it.

  The icon shows the theme you would switch TO: a moon in light mode, a sun in
  dark mode. Which icon is visible is decided by CSS from the same three-state
  rules as the colour tokens, so it is right before JavaScript runs and never
  flashes the wrong one.

  It is a toggle button: aria-pressed="true" means dark mode is on. The
  accessible name ("Mode gelap" / "Dark mode") stays constant and the pressed
  state carries the change, rather than swapping the label on every click.
--}}
<button type="button"
        data-theme-toggle
        aria-pressed="false"
        aria-label="{{ __('theme.toggle') }}"
        title="{{ __('theme.toggle') }}"
        class="theme-toggle flex min-h-11 min-w-11 items-center justify-center rounded-pill border border-line text-ink-muted hover:text-ink">
  <svg class="theme-toggle__moon" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24"
       fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
  </svg>
  <svg class="theme-toggle__sun" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24"
       fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="4"/>
    <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
  </svg>
</button>
