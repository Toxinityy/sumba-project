{{-- resources/views/components/cards/tier.blade.php --}}
@props(['title', 'cost', 'costApprox' => null, 'description', 'image'])

<div class="flex flex-col overflow-hidden rounded-lg border border-line bg-raised">
  <div class="aspect-[3/2] overflow-hidden">
    <x-picture
      :sources="$image['sources']"
      :width="$image['width']"
      :height="$image['height']"
      :alt="$image['alt']"
      sizes="(max-width: 640px) 100vw, 33vw"
      class="h-full w-full object-cover" />
  </div>

  <div class="flex flex-1 flex-col gap-2 p-6">
    <p class="font-display text-[22px] leading-tight text-ink">{{ $title }}</p>

    {{--
      Cost is stored and displayed in IDR. On the English locale only, an
      approximate USD equivalent (`costApprox`) appears beside it, from a
      manually-set rate, explicitly labelled "approx.". A bare IDR figure
      gives an overseas individual donor no sense of scale — a manual
      rate avoids an exchange-rate API, a scheduled job, and a stale-data
      bug nobody notices for months. Rate conversion logic itself belongs
      to the caller, not this component: pass the already-converted string.
    --}}
    <p class="text-[14px] font-bold text-accent">
      {{ $cost }}@if ($costApprox) <span class="font-semibold text-ink-muted">({{ $costApprox }})</span>@endif
    </p>

    <p class="text-[15px] leading-relaxed text-ink-muted">{{ $description }}</p>
  </div>
</div>
