{{-- resources/views/pages/impact.blade.php --}}
{{-- Spine per design spec §5: Hero → Stat band → Stories → Evidence →
     Next step. Deferred past launch (spec §10) — Impact needs verified
     statistics, which don't exist yet: a vague impact page is what a
     due-diligence reviewer uses to decide an organisation isn't ready.
     Built here so the whole site is clickable for review, not because
     launch scope changed; the evidence pair below is explicitly labelled
     placeholder rather than padded out to look substantial.

     $stats and $stories reuse StatData/PostData exactly as the brief
     asks. Evidence has no model yet (spec §6's Project/Impact content
     doesn't exist), so its before/after pair is built inline from
     PlaceholderImage + lang strings — the same pattern every other
     inline hero image on this page set already uses, not a new
     ViewModel for one placeholder pair. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('impact.hero.image_alt')))
@php($evidence = [
    'before' => \App\ViewModels\PlaceholderImage::make(1200, 800, __('impact.evidence.before_alt'))
        + ['caption' => __('impact.evidence.before_caption')],
    'after' => \App\ViewModels\PlaceholderImage::make(1200, 800, __('impact.evidence.after_alt'))
        + ['caption' => __('impact.evidence.after_caption')],
])

<x-layouts.site :title="__('impact.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('impact.hero.heading')"
    :subhead="__('impact.hero.body')"
    :image="$heroImage" />

  <x-sections.stat-band :stats="$stats" />

  <x-sections.stories :label="__('impact.stories.label')" :heading="__('impact.stories.heading')" :stories="$stories" />

  <x-sections.evidence
    :label="__('impact.evidence.label')"
    :heading="__('impact.evidence.heading')"
    :before="$evidence['before']"
    :after="$evidence['after']" />

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="\App\Support\LocalizedUrl::contact()"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
