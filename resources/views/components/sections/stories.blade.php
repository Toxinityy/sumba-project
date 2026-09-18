{{-- resources/views/components/sections/stories.blade.php --}}
@props([
    'stories',
    'label' => null,
    'heading' => null,
    'en' => null,
    // Geometry (spec §5). null keeps the three-up card grid the deep pages
    // use. 'stagger' is the landing page's version: three uneven columns at
    // three different heights and three different aspect ratios, set
    // typographically rather than as cards.
    'variant' => null,
    'pad' => null,
    'tone' => null,
    'plate' => null,
    // A trailing link out of the section. Same slot as the other two.
    'more' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-raised')
@php($wrap = $variant ? 'ed-wrap' : 'mx-auto max-w-content px-4')
{{-- Three ratios, three tones: the stagger reads as three different
     photographs rather than three instances of one component. --}}
@php($ratios = ['3/4', '1/1', '4/5'])
@php($tones = ['grass', 'field', 'dusk'])

<section class="{{ $shell }} {{ $ground }}">
  <div class="{{ $wrap }}">
    @if ($label || $heading)
      <div @class(['mb-10 md:mb-14' => $variant === 'stagger', 'mb-12 flex max-w-prose flex-col gap-4' => $variant !== 'stagger'])>
        @if ($label)
          <p @class(['ed-label mb-5' => $variant === 'stagger', 'text-caption uppercase tracking-[0.08em] text-ink-muted' => $variant !== 'stagger'])>{{ $label }}</p>
        @endif
        @if ($heading)
          <h2 @class(['ed-h2 max-w-[24ch]' => $variant === 'stagger', 'font-display text-h2 text-ink [text-wrap:balance]' => $variant !== 'stagger'])>
            {{ $heading }}
            @if ($en)<span class="ed-en" lang="{{ __('meta.other_locale') }}">{{ $en }}</span>@endif
          </h2>
        @endif
      </div>
    @endif

    @if ($variant === 'stagger')
      <div class="ed-g12 ed-stories">
        @foreach ($stories as $i => $story)
          <article class="ed-story ed-rise">
            @if ($plate)
              <x-plate :variant="$tones[$i % 3]" :ratio="$ratios[$i % 3]" :caption="$story['image']['alt'] ?? null" />
            @else
              <x-picture
                :sources="$story['image']['sources']" :width="$story['image']['width']"
                :height="$story['image']['height']" :alt="$story['image']['alt']"
                sizes="(max-width: 760px) 100vw, 33vw" class="rounded-lg" />
            @endif

            @if (! empty($story['kind']))
              <p class="ed-kind mt-4">{{ __('story.kind.' . $story['kind']) }}</p>
            @endif

            {{-- Safeguarding: children are named by FIRST NAME ONLY, and the
                 name is never concatenated with a surname here. The hook is a
                 separate string from the name for exactly that reason. --}}
            <h3 class="ed-h3 mt-2">
              <a href="{{ $story['href'] }}" class="no-underline hover:underline">{{ $story['name'] }}</a>
            </h3>
            <p class="ed-muted mt-2 text-[0.98rem]">{{ $story['hook'] }}</p>
          </article>
        @endforeach
      </div>
    @else
      <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
        @foreach ($stories as $story)
          <x-cards.story
            :href="$story['href']"
            :name="$story['name']"
            :hook="$story['hook']"
            :image="$story['image']" />
        @endforeach
      </div>
    @endif

    @if ($more)
      <p class="mt-8 md:mt-10">{{ $more }}</p>
    @endif
  </div>
</section>
