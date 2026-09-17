{{-- resources/views/components/sections/quote.blade.php --}}
@props(['attribution', 'role' => null])

<section class="bg-sunk py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <figure class="mx-auto flex max-w-prose flex-col gap-5 text-center">
      <blockquote class="font-display text-[24px] italic leading-[1.5] text-ink md:text-[30px]">
        {{ $slot }}
      </blockquote>
      <figcaption class="text-[13px] font-bold uppercase not-italic tracking-[0.06em] text-ink-muted">
        {{ $attribution }}@if ($role) — {{ $role }}@endif
      </figcaption>
    </figure>
  </div>
</section>
