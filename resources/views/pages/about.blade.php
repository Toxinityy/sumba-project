{{-- resources/views/pages/about.blade.php --}}
{{-- Spine per design spec §5: Hero → Our story → Founder → Mission & vision
     → People → Next step.

     "Our story", "Founder" and "Mission & vision" are spine entries, not
     section components — the thirteen have no component by those names.
     Each maps onto the section whose anatomy already fits: lede for the
     story, work (image + text, sunk) for the founder, lede again for
     mission & vision. No components/** file was added or edited.

     The two portrait/quote sections name adults in full with their role,
     which the safeguarding rules permit; the first-name-only rule binds
     minors. See docs/agent-a-pages-report.md.

     I5: image/portrait assembly moved to App\ViewModels\AboutData — this
     template now only consumes $about, matching docs/data-contract.md's
     "No Blade template should change at integration time" for every other
     launch page. --}}
<x-layouts.site :title="__('about.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('about.hero.heading')"
    :subhead="__('about.hero.body')"
    :image="$about['heroImage']" />

  <x-sections.lede :label="__('about.story.label')" :heading="__('about.story.heading')" surface="raised">
    <p>{{ __('about.story.body1') }}</p>
    <p class="mt-4">{{ __('about.story.body2') }}</p>
  </x-sections.lede>

  <x-sections.work :label="__('about.founder.label')" :heading="__('about.founder.heading')" :image="$about['founderImage']">
    <p>{{ __('about.founder.body') }}</p>
  </x-sections.work>

  {{-- Mission and vision share one section rather than sitting in two
       adjacent lede sections: spec §5 rule 1 forbids two text-dominant
       sections in a row, and vision is a subhead of the same idea. --}}
  <x-sections.lede :label="__('about.mission.label')" :heading="__('about.mission.heading')" surface="raised">
    <p>{{ __('about.mission.body') }}</p>
    <h3 class="mt-8 font-display text-h3 text-ink [text-wrap:balance]">{{ __('about.vision.heading') }}</h3>
    <p class="mt-4">{{ __('about.vision.body') }}</p>
  </x-sections.lede>

  <x-sections.people
    :label="__('about.people.label')"
    :heading="__('about.people.heading')"
    :portraits="$about['people']" />

  {{-- A Quote section (Maria Bulu) sat here through Pass 2. The corrected
       spine (spec §5, 2026-09-17) ends the page at People — six sections,
       not seven — so it's removed: not because the quote was wrong, but
       because the spine names an exact sequence and a seventh section is
       exactly the drift the fixed section table exists to prevent. --}}

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="\App\Support\LocalizedUrl::contact()"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
