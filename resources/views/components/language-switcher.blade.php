{{-- resources/views/components/language-switcher.blade.php --}}
@php($alternates = \App\Support\LocalizedUrl::alternates())

<div class="inline-flex overflow-hidden rounded-pill border border-line"
     role="group"
     aria-label="{{ __('Bahasa / Language') }}">
  @foreach ($alternates as $locale => $url)
    <a href="{{ $url }}"
       hreflang="{{ $locale }}"
       @class([
         'px-3 py-2 text-caption font-bold uppercase tracking-[0.08em]',
         'bg-accent text-accent-ink' => $locale === app()->getLocale(),
         'text-ink-muted' => $locale !== app()->getLocale(),
       ])
       @if ($locale === app()->getLocale()) aria-current="page" @endif>
      {{ strtoupper($locale) }}
    </a>
  @endforeach
</div>
