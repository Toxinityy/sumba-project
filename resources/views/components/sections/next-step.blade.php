{{-- resources/views/components/sections/next-step.blade.php --}}
@props(['heading', 'body', 'partnerHref', 'giveHref'])

<section class="bg-inverse py-14 text-inverse-ink md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid items-center gap-8 md:grid-cols-2">
      <div class="flex flex-col gap-4">
        <h2 class="font-display text-h2 [text-wrap:balance]">{{ $heading }}</h2>
        <p class="max-w-prose text-inverse-ink-muted">{{ $body }}</p>
      </div>

      {{--
        Spec §5: "Partner with us" MUST come first in the DOM. A CSR
        department — the primary audience for this site — cannot click
        Donate; it needs a proposal, a budget line and a named contact.
        Giving ("Support a school") is the secondary action below it, and
        routes to an information page (bank transfer, QRIS, Wise, PayPal),
        never a payment gateway. Do not reorder these two elements.
      --}}
      <div class="flex flex-wrap gap-4">
        <x-button :href="$partnerHref" variant="primary">{{ __('cta.partner') }}</x-button>
        <a href="{{ $giveHref }}"
           class="inline-flex min-h-11 items-center justify-center rounded-pill border-[1.5px] border-inverse-ink px-7 py-4 text-[15px] font-bold leading-none text-inverse-ink transition-colors hover:bg-inverse-ink hover:text-inverse">
          {{ __('cta.give') }}
        </a>
      </div>
    </div>
  </div>
</section>
