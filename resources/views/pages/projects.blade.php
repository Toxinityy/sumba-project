{{-- resources/views/pages/projects.blade.php --}}
{{-- Spine per design spec §5: Hero → Work → Evidence → Detail panel
     (status) → Next step. Deferred past launch (spec §10) — Projects
     needs documented before/after pairs, which don't exist yet. Built
     here so the whole site is clickable for review, not because launch
     scope changed; every image and figure below is explicitly labelled
     placeholder.

     No Project model or fixture exists (spec §6 lists Project's shape,
     but nothing produces it), so — same reasoning as impact.blade.php —
     this is one static placeholder project built inline from
     PlaceholderImage + lang strings rather than a new ViewModel class for
     a single page with no directory to build from.

     Detail panel's `status` is a qualitative construction-stage sentence,
     never a number or a funding figure — same rule as School/Home. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('projects.hero.image_alt')))
@php($workImage = \App\ViewModels\PlaceholderImage::make(1200, 800, __('projects.work.image_alt')))
@php($evidence = [
    'before' => \App\ViewModels\PlaceholderImage::make(1200, 800, __('projects.evidence.before_alt'))
        + ['caption' => __('projects.evidence.before_caption')],
    'after' => \App\ViewModels\PlaceholderImage::make(1200, 800, __('projects.evidence.after_alt'))
        + ['caption' => __('projects.evidence.after_caption')],
])

<x-layouts.site :title="__('projects.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('projects.hero.heading')"
    :subhead="__('projects.hero.body')"
    :image="$heroImage" />

  <x-sections.work :heading="__('projects.work.heading')" :image="$workImage">
    <p>{{ __('projects.work.body') }}</p>
  </x-sections.work>

  <x-sections.evidence :before="$evidence['before']" :after="$evidence['after']" />

  <x-sections.current-need
    :label="__('projects.detail.label')"
    :heading="__('projects.detail.heading')"
    :status="__('projects.detail.status')"
    :facts="[
        ['key' => __('projects.detail.location_key'), 'value' => __('projects.detail.location_value')],
        ['key' => __('projects.detail.timeline_key'), 'value' => __('projects.detail.timeline_value')],
        ['key' => __('projects.detail.partner_key'), 'value' => __('projects.detail.partner_value')],
    ]">
    <p>{{ __('deferred.note') }}</p>
  </x-sections.current-need>

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="\App\Support\LocalizedUrl::contact()"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
