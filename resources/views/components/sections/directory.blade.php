{{-- resources/views/components/sections/directory.blade.php --}}
@props(['schools', 'label' => null, 'heading' => null])

<section class="bg-raised py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    {{-- No filtering or pagination at launch: content volume is small.
         The grid grows without a rewrite when it isn't. --}}
    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
      @foreach ($schools as $school)
        <x-cards.school
          :href="$school['href']"
          :level="$school['level']"
          :name="$school['name']"
          :location="$school['location']"
          :need="$school['need']"
          :status="$school['status']"
          :image="$school['image']" />
      @endforeach
    </div>
  </div>
</section>
