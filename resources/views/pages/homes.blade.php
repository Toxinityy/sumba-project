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
     change, same as School's current-need. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('homes.hero.image_alt')))
@php($contextImage = \App\ViewModels\PlaceholderImage::make(1200, 800, __('homes.context.image_alt')))
@php($careImage = \App\ViewModels\PlaceholderImage::make(1200, 800, __('homes.care.image_alt')))
@php($parents = [
    ['name' => __('homes.people.parent1')] + \App\ViewModels\PlaceholderImage::make(800, 1000, __('homes.people.parent1_alt')),
    ['name' => __('homes.people.parent2')] + \App\ViewModels\PlaceholderImage::make(800, 1000, __('homes.people.parent2_alt')),
])
@php($before = \App\ViewModels\PlaceholderImage::make(1200, 800, __('homes.evidence.before_alt')) + ['caption' => __('homes.evidence.before_caption')])
@php($after = \App\ViewModels\PlaceholderImage::make(1200, 800, __('homes.evidence.after_alt')) + ['caption' => __('homes.evidence.after_caption')])
@php($facts = [
    ['key' => __('homes.facts.homes_key'), 'value' => __('homes.facts.homes_value')],
    ['key' => __('homes.facts.children_key'), 'value' => __('homes.facts.children_value')],
    ['key' => __('homes.facts.parents_key'), 'value' => __('homes.facts.parents_value')],
    ['key' => __('homes.facts.cost_key'), 'value' => __('homes.facts.cost_value')],
])

<x-layouts.site :title="__('homes.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('homes.hero.heading')"
    :image="$heroImage" />

  <x-sections.lede :label="__('homes.hero.label')" surface="raised">
    <p>{{ __('homes.hero.body') }}</p>
  </x-sections.lede>

  <x-sections.people
    :label="__('homes.people.label')"
    :heading="__('homes.people.heading')"
    :portraits="$parents" />

  <x-sections.context :label="__('homes.hero.label')" :heading="__('homes.context.heading')" :image="$contextImage">
    <p>{{ __('homes.context.body') }}</p>
  </x-sections.context>

  <x-sections.work :label="__('homes.care.label')" :heading="__('homes.care.heading')" :image="$careImage">
    <p>{{ __('homes.care.body') }}</p>
  </x-sections.work>

  <x-sections.evidence :label="__('homes.evidence.label')" :heading="__('homes.evidence.heading')" :before="$before" :after="$after" />

  <x-sections.current-need :label="__('homes.privacy.label')" :heading="__('homes.privacy.heading')" :status="__('homes.status')" :facts="$facts">
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
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
