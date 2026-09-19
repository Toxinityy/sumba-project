{{-- resources/views/components/sections/evidence.blade.php --}}
@props([
    'before',
    'after',
    'label' => null,
    'heading' => null,
    'en' => null,
    // Geometry (spec §5). null keeps the two equal halves the deep pages use;
    // 'offset' staggers the pair so the "before" starts lower than the "after",
    // reading as one timeline rather than two panels.
    'variant' => null,
    'pad' => null,
    'tone' => null,
    // 'pair' renders tonal plates: dusk for the before, grass for the after.
    'plate' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-surface')
@php($wrap = $variant ? 'ed-wrap' : 'mx-auto max-w-content px-4')

{{-- Before/after pairs are the highest-converting content on a fundraising
     site. Captions carry dates because an undated "after" proves nothing
     to a due-diligence reader. --}}
<section class="{{ $shell }} {{ $ground }}">
  <div class="{{ $wrap }}">
    @if ($label || $heading)
      <div @class(['mb-10 md:mb-14' => $variant === 'offset', 'mb-12 flex max-w-prose flex-col gap-4' => $variant !== 'offset'])>
        @if ($label)
          <p @class(['ed-label mb-5' => $variant === 'offset', 'text-caption uppercase tracking-[0.08em] text-ink-muted' => $variant !== 'offset'])>{{ $label }}</p>
        @endif
        @if ($heading)
          <h2 @class(['ed-h2 max-w-[24ch]' => $variant === 'offset', 'font-display text-h2 text-ink [text-wrap:balance]' => $variant !== 'offset'])>
            {{ $heading }}
            @if ($en)<span class="ed-en" lang="{{ __('meta.other_locale') }}">{{ $en }}</span>@endif
          </h2>
        @endif
      </div>
    @endif

    <div @class(['ed-g12 ed-evid' => $variant === 'offset', 'grid gap-4 md:grid-cols-2' => $variant !== 'offset'])>
      @foreach ([$before, $after] as $i => $step)
        <figure @class(['m-0 flex flex-col gap-2', 'ed-rise' => $variant === 'offset', $i === 0 ? 'ed-evid__a' : 'ed-evid__b' => $variant === 'offset'])>
          @if ($plate)
            <x-plate :variant="$i === 0 ? 'dusk' : 'grass'" ratio="3/2" :caption="$step['alt'] ?? null" />
          @else
            <x-picture
              :sources="$step['sources']"
              :width="$step['width']"
              :height="$step['height']"
              :alt="$step['alt']"
              sizes="(max-width: 768px) 100vw, 50vw"
              class="rounded" />
          @endif

          @if ($variant === 'offset')
            {{-- The date is set in Newsreader and given its own colour, so the
                 two captions read as marks on a shared timeline. Captions in
                 the fixtures are "<date> — <what changed>"; the split is on
                 that em dash and degrades to a plain caption without one. --}}
            @php([$when, $what] = array_pad(explode(' — ', $step['caption'], 2), 2, null))
            <figcaption class="ed-muted mt-1 text-[0.92rem]">
              <b>{{ $when }}</b>{{ $what }}
            </figcaption>
          @else
            <figcaption class="text-[13px] font-semibold text-ink-muted">{{ $step['caption'] }}</figcaption>
          @endif
        </figure>
      @endforeach
    </div>
  </div>
</section>
