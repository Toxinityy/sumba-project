{{-- resources/views/components/sections/partners.blade.php --}}
@props([
    'partners',
    'heading' => null,
    // Geometry (spec §5). null keeps the logo row the deep pages use.
    // 'marks' is the landing page's version: a short hairline-bounded band,
    // deliberately the fastest section on the page, with typographic monograms
    // instead of logo files.
    'variant' => null,
    'pad' => null,
    'tone' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-surface')
@php($wrap = $variant ? 'ed-wrap' : 'mx-auto max-w-content px-4')

@if ($variant === 'marks')
  {{-- Monograms, not logos. Real partner names and marks need written
       permission before they appear on a public page, and a band of empty
       boxes reads worse than a band of honest placeholders. Initials are
       derived from the partner name so the marks change with the data. --}}
  <section class="ed-partners {{ $shell }} {{ $ground }}">
    <div class="{{ $wrap }} ed-partners__in">
      <div>
        @if ($heading)<h2 class="ed-h3">{{ $heading }}</h2>@endif
        <div class="ed-muted mt-3 max-w-[40ch] text-[0.95rem]">{{ $slot }}</div>
      </div>
      <div class="flex flex-wrap gap-3 md:gap-6" role="list" aria-label="{{ __('partners.marks_label') }}">
        @foreach ($partners as $partner)
          {{-- First and LAST word, not the first two: the numbered placeholder
               names ("Contoh Mitra 1") would otherwise all monogram to "CM"
               and the band would look broken rather than provisional. --}}
          @php($words = preg_split('/\s+/', trim($partner['name'])))
          <div class="ed-mark ed-mono" role="listitem">
            <span class="sr-only">{{ $partner['name'] }}</span>
            <span aria-hidden="true">{{ mb_substr($words[0], 0, 1).(count($words) > 1 ? mb_substr(end($words), 0, 1) : '') }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@else
  <section class="{{ $shell }} {{ $ground }}">
    <div class="{{ $wrap }}">
      @if ($heading)
        <h2 class="mb-12 font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
      @endif

      {{-- Plain <img>, not <x-picture>: partner logos are flat graphics, not
           photographs, and won't have been through the variant pipeline.
           <x-picture> throws without a jpeg srcset. --}}
      <ul class="flex flex-wrap items-center gap-12">
        @foreach ($partners as $partner)
          <li>
            <img src="{{ $partner['logo'] }}"
                 alt="{{ $partner['name'] }}"
                 width="160" height="60"
                 loading="lazy"
                 class="h-12 w-auto">
          </li>
        @endforeach
      </ul>
    </div>
  </section>
@endif
