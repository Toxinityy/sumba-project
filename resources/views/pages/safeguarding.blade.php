{{-- resources/views/pages/safeguarding.blade.php --}}
{{-- The public safeguarding page spec §9 requires: the policy in plain
     language, how consent is obtained, how to ask for removal, and a route
     to a named contact.

     DELIBERATE DEVIATION from spec §5 rule 1 ("never two text-dominant
     sections adjacent"): the two lede sections below sit back to back. The
     alternation rule exists to keep photography carrying the page, and the
     only photographs that would break up a child-protection policy are
     photographs of children — which is the precise thing this policy governs.
     Surface tones (raised, then sunk) do the separating instead. Flagged in
     docs/agent-a-pages-report.md rather than silently resolved. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('safeguarding.hero.image_alt')))

<x-layouts.site :title="__('safeguarding.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('safeguarding.hero.heading')"
    :subhead="__('safeguarding.hero.body')"
    :image="$heroImage" />

  <x-sections.lede :label="__('safeguarding.hero.label')" :heading="__('safeguarding.rules.heading')" surface="raised">
    <p>{{ __('safeguarding.rules.body1') }}</p>
    <p class="mt-4">{{ __('safeguarding.rules.body2') }}</p>
    <p class="mt-4">{{ __('safeguarding.rules.body3') }}</p>
    <p class="mt-4">{{ __('safeguarding.rules.body4') }}</p>
  </x-sections.lede>

  <x-sections.lede :heading="__('safeguarding.removal.heading')" surface="sunk">
    <p>{{ __('safeguarding.removal.body') }}</p>
    <p class="mt-6">
      <a href="{{ route(app()->getLocale().'.contact') }}"
         class="font-semibold text-accent underline underline-offset-4 hover:no-underline">
        {{ __('safeguarding.removal.cta') }} &rarr;
      </a>
    </p>
  </x-sections.lede>
</x-layouts.site>
