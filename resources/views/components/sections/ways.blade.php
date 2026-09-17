{{-- resources/views/components/sections/ways.blade.php --}}
@props(['ways', 'label' => null, 'heading' => null])

<section class="bg-sunk py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    {{-- Three audience-segmented columns (spec §5: corporate, church,
         volunteer). Stacks to one column on mobile with no fixed widths, so
         Indonesian body copy at 15-20% longer than English still wraps. --}}
    <div class="grid gap-8 md:grid-cols-3">
      @foreach ($ways as $way)
        <div class="flex max-w-prose flex-col gap-4">
          <h3 class="font-display text-h3 text-ink">{{ $way['heading'] }}</h3>
          <p class="text-body text-ink">{{ $way['body'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
