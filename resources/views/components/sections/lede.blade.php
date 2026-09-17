{{-- resources/views/components/sections/lede.blade.php --}}
@props(['label' => null, 'heading' => null, 'surface' => 'raised'])

<section @class([
    'py-14 md:py-24',
    'bg-surface' => $surface === 'surface',
    'bg-raised' => $surface === 'raised',
    'bg-sunk' => $surface === 'sunk',
])>
  <div class="mx-auto max-w-content px-4">
    {{-- Prose stays in the 68ch measure while photography runs full-bleed.
         That contrast is what makes the images read as the page's substance. --}}
    <div class="flex max-w-prose flex-col gap-6">
      @if ($label)
        <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
      @endif
      @if ($heading)
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
      @endif
      <div class="text-body text-ink">{{ $slot }}</div>
    </div>
  </div>
</section>
