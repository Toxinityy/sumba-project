{{-- resources/views/components/sections/directory.blade.php --}}
{{-- Spec §5: Directory is "card grid - schools, homes, projects, or
     sponsorship tiers". `cards` names which card partial renders the grid,
     defaulting to 'school' so both existing callers (the schools directory
     page and the gallery) keep rendering exactly as before with no change
     at their call sites. 'tier' is the only other value implemented so
     far, for Get Involved's sponsorship tiers. --}}
@props([
    'schools' => [],
    'cards' => 'school',
    'label' => null,
    'heading' => null,
    'en' => null,
    // Geometry (spec §5). null keeps the three-up card grid. 'rail' is the
    // landing page's treatment: one lead school given a card of its own that
    // overlaps the section above, then the rest as an alternating rail list.
    'variant' => null,
    'pad' => null,
    'tone' => null,
    'plate' => null,
    // 'rail' only: the one school lifted out of the list and given the lead
    // treatment. Takes the detail shape (it uses `lede`), not just directory.
    'featured' => null,
    // 'rail' only: a trailing link to the full directory.
    'more' => null,
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-raised')
@php($wrap = $variant ? 'ed-wrap' : 'mx-auto max-w-content px-4')
@php($tones = ['grass', 'field', 'dusk'])

<section class="{{ $shell }} {{ $ground }}">
  <div class="{{ $wrap }}">

    @if ($variant === 'rail' && $featured)
      {{-- OVERLAP 2 of 3: this card is pulled UP across the lower boundary of
           the dark chapter above, so the two sections interlock instead of
           abutting. The chapter above carries an extra floor (pad-close) so
           the lift can never reach the pull quote. --}}
      <article class="ed-lead-school">
        <div class="ed-lead-school__body">
          <x-level-ladder :level="$featured['level']" :ageRange="$featured['age_range'] ?? null" />
          <div class="ed-lead-school__text">
            <p class="ed-where">{{ $featured['location'] }}</p>
            <h3 class="ed-h3 mt-1">{{ $featured['name'] }}</h3>
            <p class="ed-need mt-3">{{ $featured['need'] }}</p>
            @if (! empty($featured['lede']))
              <p class="ed-muted mt-3 text-[1rem]">{{ $featured['lede'] }}</p>
            @endif
            {{-- Qualitative status only: no goal, no amount raised, no bar, no
                 percentage. --}}
            <p class="ed-state mt-4">{{ $featured['status'] }}</p>
            <p class="mt-5">
              <a href="{{ $featured['href'] }}" class="ed-txtlink">
                {{ __('school.profile_label') }}
              </a>
            </p>
          </div>
        </div>
        @if ($plate)
          <x-plate variant="field" ratio="3/4" :caption="$featured['image']['alt'] ?? null" />
        @else
          <x-picture
            :sources="$featured['image']['sources']" :width="$featured['image']['width']"
            :height="$featured['image']['height']" :alt="$featured['image']['alt']"
            sizes="(max-width: 900px) 100vw, 40vw" class="rounded" />
        @endif
      </article>
    @endif

    @if ($label || $heading)
      <div @class([
        'mt-14 md:mt-20 mb-10 md:mb-14' => $variant === 'rail' && $featured,
        'mb-10 md:mb-14' => $variant === 'rail' && ! $featured,
        'mb-12 flex max-w-prose flex-col gap-4' => $variant !== 'rail',
      ])>
        @if ($label)
          <p @class(['ed-label mb-5' => $variant === 'rail', 'text-caption uppercase tracking-[0.08em] text-ink-muted' => $variant !== 'rail'])>{{ $label }}</p>
        @endif
        @if ($heading)
          <h2 @class(['ed-h2 max-w-[26ch]' => $variant === 'rail', 'font-display text-h2 text-ink [text-wrap:balance]' => $variant !== 'rail'])>
            {{ $heading }}
            @if ($en)<span class="ed-en" lang="{{ __('meta.other_locale') }}">{{ $en }}</span>@endif
          </h2>
        @endif
      </div>
    @endif

    @if ($variant === 'rail')
      {{-- A rail list, not a third 3-up grid. Rows alternate which side the
           plate sits on, and the level ladder's lit rung moves down the rail
           as you scan — that is the whole reason the ladder shows all three
           rungs every time. --}}
      <div class="ed-schoollist">
        @foreach ($schools as $i => $school)
          <article @class(['ed-schoolrow', 'ed-schoolrow--flip' => $i % 2 === 1])>
            @if ($i % 2 === 1)
              <div class="ed-schoolrow__plate">
                <x-plate :variant="$tones[$i % 3]" ratio="16/9" :caption="$school['image']['alt'] ?? null" />
              </div>
            @endif

            <x-level-ladder :level="$school['level']" :ageRange="$school['age_range'] ?? null" />

            <div>
              <p class="ed-where">{{ $school['location'] }}</p>
              <h3 class="ed-h3 mt-1">
                <a href="{{ $school['href'] }}" class="no-underline hover:underline">{{ $school['name'] }}</a>
              </h3>
              <p class="ed-need mt-2">{{ $school['need'] }}</p>
              <p class="ed-state mt-3">{{ $school['status'] }}</p>
            </div>

            @if ($i % 2 === 0)
              <div class="ed-schoolrow__plate">
                <x-plate :variant="$tones[$i % 3]" ratio="16/9" :caption="$school['image']['alt'] ?? null" />
              </div>
            @endif
          </article>
        @endforeach
      </div>

      @if ($more)
        <p class="mt-8 md:mt-10">{{ $more }}</p>
      @endif

    @else
      {{-- No filtering or pagination at launch: content volume is small.
           The grid grows without a rewrite when it isn't. --}}
      <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
        @if ($cards === 'tier')
          @foreach ($schools as $tier)
            <x-cards.tier
              :title="$tier['title']"
              :description="$tier['description']"
              :image="$tier['image']" />
          @endforeach
        @else
          @foreach ($schools as $school)
            <x-cards.school
              :href="$school['href']"
              :level="$school['level']"
              :ageRange="$school['age_range'] ?? null"
              :name="$school['name']"
              :location="$school['location']"
              :need="$school['need']"
              :status="$school['status']"
              :image="$school['image']" />
          @endforeach
        @endif
      </div>
    @endif
  </div>
</section>
