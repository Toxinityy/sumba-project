{{-- resources/views/components/sections/evidence.blade.php --}}
@props(['before', 'after', 'label' => null, 'heading' => null])

{{-- Before/after pairs are the highest-converting content on a fundraising
     site. Captions carry dates because an undated "after" proves nothing
     to a due-diligence reader. --}}
<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
      @foreach ([$before, $after] as $step)
        <figure class="flex flex-col gap-2">
          <x-picture
            :sources="$step['sources']"
            :width="$step['width']"
            :height="$step['height']"
            :alt="$step['alt']"
            sizes="(max-width: 768px) 100vw, 50vw"
            class="rounded" />
          <figcaption class="text-[13px] font-semibold text-ink-muted">{{ $step['caption'] }}</figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>
