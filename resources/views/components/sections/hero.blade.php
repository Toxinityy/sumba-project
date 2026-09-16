{{-- resources/views/components/sections/hero.blade.php --}}
@props(['heading', 'subhead' => null, 'image', 'actions' => null])

<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid items-center gap-12 md:grid-cols-2">
      <div class="flex flex-col gap-8">
        <div class="flex flex-col gap-4">
          <h1 class="font-display text-[36px] leading-[1.1] tracking-[-0.015em] text-ink md:text-display [text-wrap:balance]">
            {{ $heading }}
          </h1>
          @if ($subhead)
            <p class="text-[19px] leading-relaxed text-ink-muted md:text-[21px]">{{ $subhead }}</p>
          @endif
        </div>
        @if ($actions)
          <div class="flex flex-wrap gap-4">{{ $actions }}</div>
        @endif
      </div>

      {{-- Eager and high priority: this is the LCP element, and deferring it
           is the single easiest way to lose the performance budget. --}}
      <x-picture
        :sources="$image['sources']"
        :width="$image['width']"
        :height="$image['height']"
        :alt="$image['alt']"
        :eager="true"
        sizes="(max-width: 768px) 100vw, 50vw"
        class="rounded" />
    </div>
  </div>
</section>
