{{-- resources/views/pages/homes.blade.php --}}
{{-- Spine per design spec §5: "same shape as School detail, stricter media
     rules" — Hero → Lede → People → Context → Work → Evidence → Detail panel
     (current need) → Next step. See resources/views/pages/school.blade.php
     for the shape this mirrors.

     STRICTER MEDIA RULES bind harder here than anywhere else on the site
     (spec §5, §9): a child resident in a home is never named — not first
     name, not initial — and the site never states why a child is in care.
     Every image alt below says "no identifiable faces" for that reason, and
     every section tells the story through adults (house parents) and the
     building itself, never through a child. People shows the house parents,
     not the children they care for. Context and Evidence follow the same
     rule the prototype's rumah-anak.html already applies: no page here
     needs a photograph of a child's face to make its point.

     No Home model or fixture exists yet (docs/data-contract.md § Home has
     the shape; there's nothing to seed it from). This page is written the
     same way about.blade.php is — lang-key-driven placeholder content, no
     ViewModel class for a single static page. When Home records exist, the
     Detail panel's facts and status become real data with no template
     change, same as School's current-need.

     I5: image/portrait/evidence/facts assembly moved to
     App\ViewModels\HomeData — this template now only consumes $home,
     matching docs/data-contract.md's "No Blade template should change at
     integration time" for every other launch page. --}}
<x-layouts.site :title="__('homes.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('homes.hero.heading')"
    :image="$home['heroImage']" />

  <x-sections.lede :label="__('homes.hero.label')" surface="raised">
    <p>{{ __('homes.hero.body') }}</p>
  </x-sections.lede>

  <x-sections.people
    :label="__('homes.people.label')"
    :heading="__('homes.people.heading')"
    :portraits="$home['people']" />

  <x-sections.context :label="__('homes.hero.label')" :heading="__('homes.context.heading')" :image="$home['contextImage']">
    <p>{{ __('homes.context.body') }}</p>
  </x-sections.context>

  <x-sections.work :label="__('homes.care.label')" :heading="__('homes.care.heading')" :image="$home['careImage']">
    <p>{{ __('homes.care.body') }}</p>
  </x-sections.work>

  <x-sections.evidence :label="__('homes.evidence.label')" :heading="__('homes.evidence.heading')" :before="$home['evidence']['before']" :after="$home['evidence']['after']" />

  <x-sections.current-need :label="__('homes.privacy.label')" :heading="__('homes.privacy.heading')" :status="$home['status']" :facts="$home['facts']">
    <p>{{ __('homes.privacy.body') }}</p>
    <p class="mt-4">
      <a href="{{ route(app()->getLocale().'.safeguarding') }}"
         class="font-semibold text-accent underline underline-offset-4 hover:no-underline">
        {{ __('homes.privacy.cta') }} &rarr;
      </a>
    </p>
  </x-sections.current-need>

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="\App\Support\LocalizedUrl::contact()"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
