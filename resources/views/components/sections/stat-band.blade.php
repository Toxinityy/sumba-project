{{-- resources/views/components/sections/stat-band.blade.php --}}
@props(['stats'])

{{-- The inverse band. In dark mode --inverse-surface is deliberately distinct
     from --surface-raised; if they collapse, this section stops separating
     from the one above it and the alternation rhythm dies. --}}
<section class="bg-inverse py-14 text-inverse-ink md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid gap-8 md:grid-cols-3">
      @foreach ($stats as $stat)
        <div>
          <p class="mb-2 font-display text-[48px] leading-none tabular-nums md:text-[64px]">
            {{ $stat['value'] }}
          </p>
          <p class="text-[15px] font-semibold leading-snug">{{ $stat['label'] }}</p>
          @if (! empty($stat['asOf']))
            {{-- as_of makes a stale number visible to the team rather than
                 quietly wrong to a reader. --}}
            <p class="mt-1.5 text-[12px] text-inverse-ink-muted">{{ $stat['asOf'] }}</p>
          @endif
        </div>
      @endforeach
    </div>
  </div>
</section>
