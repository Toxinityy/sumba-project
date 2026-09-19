{{-- resources/views/layouts/site.blade.php --}}
@php($alternates = \App\Support\LocalizedUrl::alternates())

<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Fallback must be useful on its own: config('app.name') defaults to the
       literal "Laravel" until APP_NAME is set, which would fail every page
       that doesn't pass $title. --}}
  <title>{{ $title ?? 'Hope for Sumba' }}</title>

  @foreach ($alternates as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
  @endforeach
  <link rel="alternate" hreflang="x-default" href="{{ $alternates[config('locales.default')] }}">

  <link rel="preload" href="/fonts/newsreader-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/fonts/public-sans-latin.woff2" as="font" type="font/woff2" crossorigin>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <script>
    /* Runs before the body parses so an explicitly chosen theme never flashes
       the other one. "system" deliberately sets nothing and lets
       prefers-color-scheme decide — that is the state most visitors are in. */
    try {
      var saved = localStorage.getItem("hfs-theme");
      if (saved === "light" || saved === "dark") {
        document.documentElement.setAttribute("data-theme", saved);
      }
    } catch (e) {}
  </script>
</head>
<body class="bg-surface text-ink font-body">
  <a href="#main"
     class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded focus:bg-accent focus:px-4 focus:py-2 focus:text-accent-ink focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-accent-strong">
    {{ __('nav.skip_to_content') }}
  </a>
  <x-site-nav />
  <main id="main">{{ $slot }}</main>
  <x-site-footer />
</body>
</html>
