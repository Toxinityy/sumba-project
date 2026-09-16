{{-- resources/views/components/sections/work.blade.php --}}
@props(['label' => null, 'heading', 'image'])

{{-- The structural mirror of context: same anatomy, opposite side and a
     sunk surface, so consecutive context/work sections alternate which
     side the photograph sits on. That alternation is the page's rhythm. --}}
<section class="bg-sunk py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid items-center gap-12 md:grid-cols-2">
      <div class="md:order-2 flex max-w-prose flex-col gap-6">
        @if ($label)
          <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
        @endif
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
        <div class="text-body text-ink">{{ $slot }}</div>
      </div>

      <div class="md:order-1">
        <x-picture
          :sources="$image['sources']"
          :width="$image['width']"
          :height="$image['height']"
          :alt="$image['alt']"
          sizes="(max-width: 768px) 100vw, 50vw"
          class="rounded" />
      </div>
    </div>
  </div>
</section>
