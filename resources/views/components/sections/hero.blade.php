{{-- resources/views/components/sections/hero.blade.php --}}
@props([
    'heading',
    'subhead' => null,
    'image' => null,
    'actions' => null,
    // Geometry props (spec §5, "section geometry is per-section, not global").
    // `variant` null keeps the centred 1200px two-column form every deep page
    // uses; 'editorial' is the landing page's asymmetric split.
    'variant' => null,
    'pad' => null,
    'tone' => null,
    // A tonal placeholder plate instead of a photograph: 'field'|'grass'|'dusk'.
    'plate' => null,
    // One word of the headline set in Newsreader italic at the accent colour.
    // Kept as its own prop rather than parsed out of $heading: the word that
    // carries the emphasis differs per language and is an editorial choice.
    'accent' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-surface')

@if ($variant === 'editorial')
  {{-- Container relationship 2 of 4: inside the wider 1440px band, not the
       1200px content width — and OVERLAP 1 of 3, the plate bleeding past the
       right gutter and hanging below the hero's own lower boundary. The
       padding is bespoke (top only), because the section below it has to be
       the thing that closes this one. --}}
  <section class="ed-hero {{ $ground }}">
    <div class="ed-wrap ed-wrap--wide">
      <div class="ed-g12 ed-hero__grid">
        <div class="ed-hero__copy">
          <div class="ed-hero__rule mb-8" aria-hidden="true"></div>
          {{-- The accented word is a SUFFIX of $heading, not a second string:
               the translation file keeps one readable sentence per locale and
               this splits it at the word the editor chose to emphasise. If the
               word is not found (a translator rephrased it), the headline
               renders whole and unemphasised rather than losing a word or
               gaining a duplicate. --}}
          @php($stem = $accent && str_contains($heading, $accent) ? \Illuminate\Support\Str::beforeLast($heading, $accent) : $heading)
          <h1 class="ed-masthead">
            {{ $stem }}@if ($accent && $stem !== $heading)<em>{{ $accent }}</em>@endif
          </h1>
          @if ($subhead)
            <p class="ed-muted mt-6 text-[clamp(1.06rem,1.6vw,1.24rem)] max-w-[34ch]">{{ $subhead }}</p>
          @endif
          @if ($actions)
            <div class="mt-8 flex flex-wrap gap-4">{{ $actions }}</div>
          @endif
        </div>

        <div class="ed-hero__plate">
          @if ($plate)
            <x-plate :variant="$plate" :caption="$image['alt'] ?? null" />
          @else
            <x-picture
              :sources="$image['sources']"
              :width="$image['width']"
              :height="$image['height']"
              :alt="$image['alt']"
              :eager="true"
              sizes="(max-width: 900px) 100vw, 42vw"
              class="h-full w-full rounded-lg object-cover" />
          @endif
        </div>
      </div>
    </div>
  </section>
@else
  <section class="{{ $shell }} {{ $ground }}">
    <div class="mx-auto max-w-content px-4">
      <div class="grid items-center gap-12 md:grid-cols-2">
        <div class="flex flex-col gap-8">
          <div class="flex flex-col gap-4">
            <h1 class="font-display text-[36px] leading-[1.1] tracking-[-0.015em] text-ink md:text-display [text-wrap:balance]">
              {{ $heading }}
            </h1>
            @if ($subhead)
              <p class="text-[19px] leading-relaxed text-ink-muted md:text-[21px]">{{ $subhead }}</p>
            @endif
          </div>
          @if ($actions)
            <div class="flex flex-wrap gap-4">{{ $actions }}</div>
          @endif
        </div>

        {{-- Eager and high priority: this is the LCP element, and deferring it
             is the single easiest way to lose the performance budget. --}}
        <x-picture
          :sources="$image['sources']"
          :width="$image['width']"
          :height="$image['height']"
          :alt="$image['alt']"
          :eager="true"
          sizes="(max-width: 768px) 100vw, 50vw"
          class="rounded" />
      </div>
    </div>
  </section>
@endif
