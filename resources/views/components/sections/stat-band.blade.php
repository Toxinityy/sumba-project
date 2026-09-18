{{-- resources/views/components/sections/stat-band.blade.php --}}
@props([
    'stats',
    // Geometry (spec §5). null keeps the three-equal-columns inverse band the
    // deep pages use. 'scale' is the landing page's one-big-plus-a-pair
    // opening of the dark chapter; 'ledger' is the challenge section, the same
    // Stat shape read as a figure ledger on a light ground.
    'variant' => null,
    'pad' => null,
    'tone' => null,
    'label' => null,
    'heading' => null,
    'en' => null,
    // A trailing link out of the section (e.g. to the deep page this section
    // summarises). Same slot name and placement as the directory and
    // stories sections, so a caller learns it once. (No angle-bracket x- tags
    // in a // comment: Blade compiles component tags AFTER stripping {{-- --}}
    // comments but BEFORE PHP runs, so one here becomes a real component.)
    'more' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-inverse text-inverse-ink')
@php($wrap = $variant ? 'ed-wrap' : 'mx-auto max-w-content px-4')

<section class="{{ $shell }} {{ $ground }}">
  <div class="{{ $wrap }}">
    @if ($label || $heading)
      <div class="mb-10 md:mb-14">
        @if ($label)<p class="ed-label mb-5">{{ $label }}</p>@endif
        @if ($heading)
          <h2 class="ed-h2 ed-prose">
            {{ $heading }}
            @if ($en)<span class="ed-en" lang="{{ __('meta.other_locale') }}">{{ $en }}</span>@endif
          </h2>
        @endif
      </div>
    @endif

    @if ($variant === 'ledger')
      {{-- A figure ledger: no cards, uneven spans, hairline rules only, each
           measure starting lower than the last. THE DIGNITY RULE applies to
           every measure here — each one is a circumstance or a gap in the
           system (distance, a vacant post, no grid electricity), never an
           attribute of a child or a family. "0 science teachers" is in scope;
           "these children are behind" is not.

           `value` is not always a number. "Setelah gelap" / "After dark" is a
           measure too, and gets the word treatment so it does not try to sit
           at 80px. --}}
      <div class="ed-g12 ed-ledger">
        @foreach ($stats as $stat)
          <div class="ed-measure ed-rise">
            <span @class(['ed-fig', 'ed-fig--word' => ! preg_match('/\d/', $stat['value'])])>{{ $stat['value'] }}</span>
            <p class="ed-muted mt-2 max-w-[34ch] text-[1rem]">
              <strong class="font-semibold text-ink">{{ $stat['label'] }}</strong>@if (! empty($stat['body'])) {{ $stat['body'] }}@endif
            </p>
          </div>
        @endforeach
      </div>

    @elseif ($variant === 'scale')
      {{-- The dark chapter opens here. The first stat is the one the page is
           about, so it is set at display scale (Fraunces wght 280 at up to
           200px — low weight at large size, spec §4) and the rest sit in a
           rule-divided pair beside it. Nothing is a funding figure. --}}
      @php($lead = $stats[0] ?? null)
      @php($rest = array_slice($stats, 1))
      <div class="ed-g12 ed-stats">
        @if ($lead)
          <div class="ed-stat--big ed-rise">
            <span class="ed-stat__fig">{{ $lead['value'] }}</span>
            <span class="mt-4 block max-w-[22ch] text-[1.2rem] font-semibold">{{ $lead['label'] }}</span>
            @if (! empty($lead['asOf']))
              <span class="ed-muted mt-2 block text-[0.78rem] font-bold uppercase tracking-[0.1em]">{{ $lead['asOf'] }}</span>
            @endif
          </div>
        @endif
        @if ($rest)
          <div class="ed-stat--pair">
            @foreach ($rest as $stat)
              <div class="ed-rise">
                <span class="ed-stat__fig">{{ $stat['value'] }}</span>
                <span class="mt-4 block max-w-[26ch] font-semibold">{{ $stat['label'] }}</span>
                @if (! empty($stat['asOf']))
                  <span class="ed-muted mt-2 block text-[0.78rem] font-bold uppercase tracking-[0.1em]">{{ $stat['asOf'] }}</span>
                @endif
              </div>
            @endforeach
          </div>
        @endif
      </div>

    @else
      {{-- The inverse band. In dark mode --inverse-surface is deliberately
           distinct from --surface-raised; if they collapse, this section stops
           separating from the one above it and the alternation rhythm dies. --}}
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
    @endif

    @if ($more)
      <p class="mt-8 md:mt-10">{{ $more }}</p>
    @endif
  </div>
</section>
