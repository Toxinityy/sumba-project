{{-- resources/views/components/sections/quote.blade.php --}}
@props([
    'attribution',
    'role' => null,
    // Geometry (spec §5). null keeps the centred pull quote the deep pages
    // use. 'voice' is the landing page's version: left-aligned, set wider than
    // the prose measure, with the opening quote mark hanging outside it, and
    // rule-joined to the section above so it closes the dark chapter rather
    // than starting a new band.
    'variant' => null,
    'pad' => null,
    'tone' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-sunk')
@php($wrap = $variant ? 'ed-wrap' : 'mx-auto max-w-content px-4')

<section class="{{ $shell }} {{ $ground }}">
  <div class="{{ $wrap }}">
    @if ($variant === 'voice')
      {{-- OVERLAP 3 of 3: the quote breaks its own measure. It is the only
           element on the page allowed to, which is what marks it as a person
           speaking rather than another block of copy. --}}
      <figure class="ed-voice m-0 pt-10 md:pt-14">
        <blockquote class="ed-quote">
          {{ $slot }}
        </blockquote>
        <figcaption class="ed-quote__by mt-8 text-[0.95rem] font-bold not-italic">
          {{ $attribution }}
          @if ($role)<span class="ed-muted block font-medium">{{ $role }}</span>@endif
        </figcaption>
      </figure>
    @else
      <figure class="mx-auto flex max-w-prose flex-col gap-5 text-center">
        <blockquote class="font-display text-[24px] italic leading-[1.5] text-ink md:text-[30px]">
          {{ $slot }}
        </blockquote>
        <figcaption class="text-[13px] font-bold uppercase not-italic tracking-[0.06em] text-ink-muted">
          {{ $attribution }}@if ($role) — {{ $role }}@endif
        </figcaption>
      </figure>
    @endif
  </div>
</section>
