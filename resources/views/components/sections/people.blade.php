{{-- resources/views/components/sections/people.blade.php --}}
@props(['portraits', 'label' => null, 'heading' => null])

<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
      @foreach ($portraits as $portrait)
        <figure class="flex flex-col gap-3">
          {{-- Environmental portraits at 4:5. A teacher in her classroom says
               more than a face on a wall. --}}
          <div class="aspect-[4/5] overflow-hidden rounded">
            <x-picture
              :sources="$portrait['sources']"
              :width="$portrait['width']"
              :height="$portrait['height']"
              :alt="$portrait['alt']"
              sizes="(max-width: 640px) 100vw, 33vw"
              class="h-full w-full object-cover" />
          </div>
          <figcaption class="font-display text-[22px] text-ink">{{ $portrait['name'] }}</figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>
