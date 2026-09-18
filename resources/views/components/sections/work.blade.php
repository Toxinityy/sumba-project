{{-- resources/views/components/sections/work.blade.php --}}
@props([
    'label' => null,
    'heading',
    'image',
    'en' => null,
    // Geometry (spec §5). null keeps the mirrored two-column form the deep
    // pages use. 'interlock' is the landing page's version: the wide plate
    // full-bleeds to the viewport's left edge and the copy beside it hangs
    // down past it into row two.
    'variant' => null,
    'pad' => null,
    'tone' => null,
    'plate' => null,
    // The interlock's second, smaller plate, sitting under the wide one.
    'imageNarrow' => null,
    'plateNarrow' => null,
    // A trailing paragraph set below the fold of the interlock, in the copy
    // column rather than under the heading. Named slot, so it can carry a link.
    'note' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-sunk')
@php($wrap = $variant ? 'ed-wrap' : 'mx-auto max-w-content px-4')

@if ($variant === 'interlock')
  <section class="{{ $shell }} {{ $ground }}">
    <div class="{{ $wrap }}">
      <div class="ed-g12 ed-work">
        {{-- Container relationship 3 of 4: full bleed to the viewport's left
             edge — the one element on the page that does not respect the
             container at all. body carries overflow-x: clip so this cannot
             produce a horizontal scrollbar. --}}
        <div class="ed-work__wide ed-rise">
          @if ($plate)
            <x-plate :variant="$plate" ratio="3/2" :caption="$image['alt'] ?? null" />
          @else
            <x-picture
              :sources="$image['sources']" :width="$image['width']"
              :height="$image['height']" :alt="$image['alt']"
              sizes="(max-width: 900px) 100vw, 50vw" class="rounded" />
          @endif
        </div>

        <div class="ed-work__lead">
          @if ($label)<p class="ed-label mb-5">{{ $label }}</p>@endif
          <h2 class="ed-h2">
            {{ $heading }}
            @if ($en)<span class="ed-en" lang="{{ __('meta.other_locale') }}">{{ $en }}</span>@endif
          </h2>
          <div class="ed-lead mt-6">{{ $slot }}</div>
        </div>

        @if ($imageNarrow || $plateNarrow)
          <div class="ed-work__narrow ed-rise">
            @if ($plateNarrow)
              <x-plate :variant="$plateNarrow" ratio="1/1" :caption="$imageNarrow['alt'] ?? null" />
            @else
              <x-picture
                :sources="$imageNarrow['sources']" :width="$imageNarrow['width']"
                :height="$imageNarrow['height']" :alt="$imageNarrow['alt']"
                sizes="(max-width: 900px) 100vw, 25vw" class="rounded" />
            @endif
          </div>
        @endif

        @if ($note)
          <div class="ed-work__note ed-muted">{{ $note }}</div>
        @endif
      </div>
    </div>
  </section>
@else
  {{-- The structural mirror of context: same anatomy, opposite side and a
       sunk surface, so consecutive context/work sections alternate which
       side the photograph sits on. That alternation is the page's rhythm. --}}
  <section class="{{ $shell }} {{ $ground }}">
    <div class="{{ $wrap }}">
      <div class="grid items-center gap-12 md:grid-cols-2">
        <div class="md:order-2 flex max-w-prose flex-col gap-6">
          @if ($label)
            <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
          @endif
          <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
          <div class="text-body text-ink">{{ $slot }}</div>
        </div>

        <div class="md:order-1">
          <x-picture
            :sources="$image['sources']"
            :width="$image['width']"
            :height="$image['height']"
            :alt="$image['alt']"
            sizes="(max-width: 768px) 100vw, 50vw"
            class="rounded" />
        </div>
      </div>
    </div>
  </section>
@endif
