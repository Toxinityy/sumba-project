{{-- resources/views/pages/give.blade.php --}}
{{-- Spine per design spec §5 (rewritten 2026-09-17, commit dc06ca3):
     Hero → Directory (sponsorship tiers) → Ways (corporate, church,
     volunteer) → Detail panel (how giving works) → Next step.

     All five sections come from the shared component set; this page adds
     no markup of its own. Directory now takes an optional `cards="tier"`
     prop (see components/sections/directory.blade.php) to render
     <x-cards.tier> instead of <x-cards.school>. Ways is a new section
     (components/sections/ways.blade.php). "How giving works" is the
     current-need component's broadened job — see spec §5, "Detail panel".

     Placeholder discipline: bank and account-number values read
     "CONTOH — belum diisi" / "PLACEHOLDER — not yet supplied"
     (give.how.placeholder), never a plausible-looking fake — spec §12
     lists the real legal entity name, registration number and bank
     details as still outstanding (owner: Reynold). --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('give.hero.heading')))

<x-layouts.site :title="__('give.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('give.hero.heading')"
    :subhead="__('give.hero.body')"
    :image="$heroImage" />

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
        ['key' => __('give.how.account_name_key'), 'value' => 'Yayasan Harapan Sumba'],
        ['key' => __('give.how.bank_key'), 'value' => __('give.how.placeholder')],
        ['key' => __('give.how.account_number_key'), 'value' => __('give.how.placeholder')],
        ['key' => __('give.how.international_key'), 'value' => 'Wise / PayPal'],
        ['key' => __('give.how.acknowledgement_key'), 'value' => __('give.how.acknowledgement_value')],
    ]">
    <p>{{ __('give.how.body1') }}</p>
    <p class="mt-4">{{ __('give.how.body2') }}</p>
  </x-sections.current-need>

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
