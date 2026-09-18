{{-- resources/views/components/sections/people.blade.php --}}
@props([
    'portraits',
    'label' => null,
    'heading' => null,
    // Geometry (spec §5). null keeps the centred 3-up grid the deep pages use;
    // 'offset' is the landing page's offset prose plus a staggered band.
    'variant' => null,
    'pad' => null,
    'tone' => null,
    // Any truthy value renders tonal placeholder plates instead of x-picture,
    // cycling field → grass → dusk so three portraits never share one tone.
    'plate' => null,
    'en' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-surface')
@php($tones = ['field', 'grass', 'dusk'])
{{-- Container: the landing variant uses the design's own 1200px wrap with a
     fluid gutter; every other caller keeps the utility container it had. --}}
@php($wrap = $variant === 'offset' ? 'ed-wrap' : 'mx-auto max-w-content px-4')

<section class="{{ $shell }} {{ $ground }}">
  <div class="{{ $wrap }}">
    @if ($variant === 'offset')
      {{-- Container relationship 4 of 4: the prose is offset INTO the grid
           (columns 4-12), not centred in it, so the section opens off-axis. --}}
      <div class="ed-g12">
        <div class="ed-offset">
          @if ($label)<p class="ed-label mb-5">{{ $label }}</p>@endif
          @if ($heading)
            <h2 class="ed-h2">
              {{ $heading }}
              @if ($en)<span class="ed-en" lang="{{ __('meta.other_locale') }}">{{ $en }}</span>@endif
            </h2>
          @endif
          <div class="ed-lead ed-prose mt-7">{{ $slot }}</div>
        </div>
      </div>
    @elseif ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    <div @class([
      'ed-portband mt-10 md:mt-14' => $variant === 'offset',
      'grid gap-8 sm:grid-cols-2 md:grid-cols-3' => $variant !== 'offset',
    ])>
      @foreach ($portraits as $i => $portrait)
        <figure class="m-0 flex flex-col gap-3 {{ $variant === 'offset' ? 'ed-rise' : '' }}">
          {{-- Environmental portraits at 4:5. A teacher in her classroom says
               more than a face on a wall. --}}
          @if ($plate)
            <x-plate :variant="$tones[$i % 3]" ratio="4/5" :caption="$portrait['alt'] ?? null" />
          @else
            <div class="aspect-[4/5] overflow-hidden rounded">
              <x-picture
                :sources="$portrait['sources']"
                :width="$portrait['width']"
                :height="$portrait['height']"
                :alt="$portrait['alt']"
                sizes="(max-width: 640px) 100vw, 33vw"
                class="h-full w-full object-cover" />
            </div>
          @endif

          {{-- Safeguarding: these are adults — staff, house parents, the
               founder — so a full name plus a role is correct here. Children
               are never listed in this section. --}}
          <figcaption>
            @if ($variant === 'offset')
              <p class="ed-name mt-1">{{ $portrait['name'] }}</p>
              @if (! empty($portrait['role']))
                <p class="ed-muted text-[0.9rem]">{{ $portrait['role'] }}</p>
              @endif
            @else
              <span class="font-display text-[22px] text-ink">{{ $portrait['name'] }}</span>
            @endif
          </figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>
