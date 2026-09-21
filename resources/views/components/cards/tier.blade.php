{{-- resources/views/components/cards/tier.blade.php --}}
{{-- Legacy cost props are consumed but never displayed. AGENTS.md forbids public funding figures. --}}
@props(['title', 'description', 'image', 'cost' => null, 'costApprox' => null])

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

    <p class="text-[15px] leading-relaxed text-ink-muted">{{ $description }}</p>
  </div>
</div>
