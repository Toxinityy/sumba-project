{{-- resources/views/components/sections/stories.blade.php --}}
@props(['stories', 'label' => null, 'heading' => null])

<section class="bg-raised py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
      @foreach ($stories as $story)
        <x-cards.story
          :href="$story['href']"
          :name="$story['name']"
          :hook="$story['hook']"
          :image="$story['image']" />
      @endforeach
    </div>
  </div>
</section>
