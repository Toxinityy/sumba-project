{{-- resources/views/pages/give.blade.php --}}
{{-- The support journey is enquiry-led until the ministry supplies verified
     payment details. The shared sections still follow the spec §5 spine. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('give.hero.heading')))

<x-layouts.site :title="__('give.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('give.hero.heading')"
    :subhead="__('give.hero.body')"
    :image="$heroImage">
    <x-slot:actions>
      <x-button :href="\App\Support\LocalizedUrl::contact()">{{ __('give.enquiry.cta') }}</x-button>
    </x-slot:actions>
  </x-sections.hero>

  <x-sections.directory
    cards="tier"
    :schools="$tiers"
    :label="__('give.hero.label')"
    :heading="__('give.tiers.heading')" />

  <x-sections.ways
    :heading="__('give.ways.heading')"
    :ways="[
        ['heading' => __('give.corporate.heading'), 'body' => __('give.corporate.body')],
        ['heading' => __('give.church.heading'), 'body' => __('give.church.body')],
        ['heading' => __('give.volunteer.heading'), 'body' => __('give.volunteer.body')],
    ]" />

  <x-sections.current-need
    :label="__('give.how.label')"
    :heading="__('give.how.heading')"
    :status="__('give.how.status')"
    :facts="[
        ['key' => __('give.how.interest_key'), 'value' => __('give.how.interest_value')],
        ['key' => __('give.how.discuss_key'), 'value' => __('give.how.discuss_value')],
        ['key' => __('give.how.next_key'), 'value' => __('give.how.next_value')],
    ]">
    <p>{{ __('give.how.body1') }}</p>
    <p class="mt-4">{{ __('give.how.body2') }}</p>
  </x-sections.current-need>

  <x-sections.next-step
    :heading="__('give.enquiry.heading')"
    :body="__('give.enquiry.body')"
    :partnerLabel="__('give.enquiry.cta')"
    :partnerHref="\App\Support\LocalizedUrl::contact()" />
</x-layouts.site>
