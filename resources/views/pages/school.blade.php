{{-- resources/views/pages/school.blade.php --}}
{{-- Spine per design spec §5: Hero → Lede → People → Context → Work →
     Evidence → Current need → Next step. $school comes from
     App\ViewModels\SchoolData::find() today; see routes/web.php and
     docs/data-contract.md. --}}
<x-layouts.site :title="$school['name'].' — Hope for Sumba'">
  <x-sections.hero
    :heading="$school['name']"
    :subhead="$school['location']"
    :image="$school['image']" />

  <x-sections.lede :label="__('school.profile_label')" surface="raised">
    <x-translation-note :from="$school['translated_from'] ?? null" />
    <p>{{ $school['lede'] }}</p>
  </x-sections.lede>

  <x-sections.people :portraits="$school['people']" />

  <x-sections.context :heading="$school['context']['heading']" :image="$school['context']['image']">
    <p>{{ $school['context']['body'] }}</p>
  </x-sections.context>

  <x-sections.work :heading="$school['work']['heading']" :image="$school['work']['image']">
    <p>{{ $school['work']['body'] }}</p>
  </x-sections.work>

  <x-sections.evidence :before="$school['evidence']['before']" :after="$school['evidence']['after']" />

  <x-sections.current-need :heading="$school['need']" :status="$school['status']" :facts="$school['facts']">
    <p>{{ $school['work']['body'] }}</p>
  </x-sections.current-need>

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="\App\Support\LocalizedUrl::contact()"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
