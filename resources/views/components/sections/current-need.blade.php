{{-- resources/views/components/sections/current-need.blade.php --}}
@props(['heading', 'status', 'facts' => [], 'label' => null])

<section class="bg-raised py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid gap-12 md:grid-cols-2">
      <div class="flex max-w-prose flex-col gap-6">
        @if ($label)
          <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
        @endif
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
        <div class="text-body text-ink">{{ $slot }}</div>
        <p class="text-[14px] font-bold text-accent">{{ $status }}</p>
      </div>

      {{-- The facts a due-diligence reader scans for (opened, pupils, teachers,
           cost to families), in a scannable list rather than buried in prose.
           No numeric funding, no progress bars, no percentages: same rule as
           the cards. --}}
      <dl class="grid gap-4">
        @foreach ($facts as $fact)
          <div class="flex justify-between gap-6 border-b border-line pb-4">
            <dt class="text-ink-muted">{{ $fact['key'] }}</dt>
            <dd class="text-right font-semibold text-ink">{{ $fact['value'] }}</dd>
          </div>
        @endforeach
      </dl>
    </div>
  </div>
</section>
