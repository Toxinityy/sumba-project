{{-- resources/views/pages/give.blade.php --}}
{{-- Spine per design spec §5: Hero → Tiers → Corporate → Church →
     Volunteer → How giving works. No "Next step" here — this whole page
     already is the giving/partnership destination.

     Tiers, and the Corporate/Church/Volunteer + "How giving works" text
     blocks, have no dedicated section component among the thirteen (the
     spine names them; the component set has nothing that fits a plain
     3-column text block or a tier grid). Rather than add or edit a
     components/** file, this page uses inline markup on the same surface
     tokens and padding rhythm the section components use, and reuses
     <x-cards.tier> for the grid. See docs/agent-a-pages-report.md. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('give.hero.heading')))

<x-layouts.site :title="__('give.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('give.hero.heading')"
    :subhead="__('give.hero.body')"
    :image="$heroImage" />

  <section class="bg-raised py-14 md:py-24">
    <div class="mx-auto max-w-content px-4">
      <p class="mb-12 text-caption uppercase tracking-[0.08em] text-ink-muted">{{ __('give.hero.label') }}</p>
      <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
        @foreach ($tiers as $tier)
          <x-cards.tier
            :title="$tier['title']"
            :cost="$tier['cost']"
            :costApprox="$tier['costApprox']"
            :description="$tier['description']"
            :image="$tier['image']" />
        @endforeach
      </div>
    </div>
  </section>

  <section class="bg-sunk py-14 md:py-24">
    <div class="mx-auto max-w-content px-4">
      <div class="grid gap-8 md:grid-cols-3">
        <div class="flex flex-col gap-4">
          <h3 class="font-display text-h3 text-ink">{{ __('give.corporate.heading') }}</h3>
          <p class="text-body text-ink">{{ __('give.corporate.body') }}</p>
        </div>
        <div class="flex flex-col gap-4">
          <h3 class="font-display text-h3 text-ink">{{ __('give.church.heading') }}</h3>
          <p class="text-body text-ink">{{ __('give.church.body') }}</p>
        </div>
        <div class="flex flex-col gap-4">
          <h3 class="font-display text-h3 text-ink">{{ __('give.volunteer.heading') }}</h3>
          <p class="text-body text-ink">{{ __('give.volunteer.body') }}</p>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-surface py-14 md:py-24">
    <div class="mx-auto max-w-content px-4">
      <div class="grid gap-12 md:grid-cols-2">
        <div class="flex max-w-prose flex-col gap-6">
          <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ __('give.how.label') }}</p>
          <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ __('give.how.heading') }}</h2>
          <p class="text-body text-ink">{{ __('give.how.body1') }}</p>
          <p class="text-body text-ink">{{ __('give.how.body2') }}</p>
        </div>

        <dl class="grid gap-4">
          <div class="flex justify-between gap-6 border-b border-line pb-4">
            <dt class="text-ink-muted">{{ __('give.how.account_name_key') }}</dt>
            <dd class="text-right font-semibold text-ink">Yayasan Harapan Sumba</dd>
          </div>
          <div class="flex justify-between gap-6 border-b border-line pb-4">
            <dt class="text-ink-muted">{{ __('give.how.bank_key') }}</dt>
            <dd class="text-right font-semibold text-ink">{{ __('give.how.placeholder') }}</dd>
          </div>
          <div class="flex justify-between gap-6 border-b border-line pb-4">
            <dt class="text-ink-muted">{{ __('give.how.account_number_key') }}</dt>
            <dd class="text-right font-semibold text-ink">{{ __('give.how.placeholder') }}</dd>
          </div>
          <div class="flex justify-between gap-6 border-b border-line pb-4">
            <dt class="text-ink-muted">{{ __('give.how.international_key') }}</dt>
            <dd class="text-right font-semibold text-ink">Wise / PayPal</dd>
          </div>
          <div class="flex justify-between gap-6 border-b border-line pb-4">
            <dt class="text-ink-muted">{{ __('give.how.acknowledgement_key') }}</dt>
            <dd class="text-right font-semibold text-ink">{{ __('give.how.acknowledgement_value') }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </section>
</x-layouts.site>
