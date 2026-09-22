{{-- resources/views/components/sections/next-step.blade.php --}}
@props([
    'heading',
    'body',
    'partnerHref',
    'giveHref' => null,
    'partnerLabel' => null,
    'en' => null,
    // Geometry (spec §5). null keeps the inverse two-column band the deep
    // pages use. 'field' is the landing page's version: a large tinted field,
    // asymmetric (5 columns of copy against 6 of ways) rather than an even
    // split, closing the page on a light ground instead of a second dark one.
    'variant' => null,
    'pad' => null,
    'tone' => null,
    // 'field' only: the audience-segmented ways in, in the right-hand column.
    // Each is ['heading' => ..., 'body' => ...]; the last may carry 'link'
    // (['href' => ..., 'label' => ...]) for the how-to-give detail page.
    'ways' => [],
])

@php($shell = $pad ? "pad-{$pad}" : 'py-14 md:py-24')
@php($ground = $tone ? "on-{$tone}" : 'bg-inverse text-inverse-ink')
@php($wrap = $variant ? 'ed-wrap' : 'mx-auto max-w-content px-4')

<section class="{{ $shell }} {{ $ground }}">
  <div class="{{ $wrap }}">
    <div @class(['ed-g12 ed-next' => $variant === 'field', 'grid items-center gap-8 md:grid-cols-2' => $variant !== 'field'])>
      <div @class(['ed-next__head' => $variant === 'field', 'flex flex-col gap-4' => $variant !== 'field'])>
        <h2 @class(['ed-h2' => $variant === 'field', 'font-display text-h2 [text-wrap:balance]' => $variant !== 'field'])>
          {{ $heading }}
          @if ($en)<span class="ed-en" lang="{{ __('meta.other_locale') }}">{{ $en }}</span>@endif
        </h2>
        <p @class(['ed-lead mt-6 max-w-[36ch]' => $variant === 'field', 'max-w-prose text-inverse-ink-muted' => $variant !== 'field'])>{{ $body }}</p>

        {{--
          Spec §5: "Partner with us" MUST come first in the DOM. A CSR
          department — the primary audience for this site — cannot click
          Donate; it needs a proposal, a budget line and a named contact.
          Giving ("Support a school") is the secondary action where present;
          the giving page itself ends with one enquiry action.
        --}}
        <div @class(['mt-8 flex flex-wrap gap-4' => $variant === 'field', 'flex flex-wrap gap-4' => $variant !== 'field'])>
          @if ($variant === 'field')
            {{-- On the tinted ground both buttons are token-safe: --accent as a
                 BACKGROUND with --accent-ink on it, and --ink text on
                 --badge-bg at 11.70:1 light / 11.53:1 dark. Accent is never
                 used as text on this ground — it would be 3.92:1. --}}
            <x-button :href="$partnerHref" variant="primary">{{ $partnerLabel ?? __('cta.partner') }}</x-button>
            @if ($giveHref)
              <x-button :href="$giveHref" variant="secondary">{{ __('cta.give') }}</x-button>
            @endif
          @else
            <x-button :href="$partnerHref" variant="primary">{{ $partnerLabel ?? __('cta.partner') }}</x-button>
            @if ($giveHref)
              <a href="{{ $giveHref }}"
                 class="inline-flex min-h-11 items-center justify-center rounded-pill border-[1.5px] border-inverse-ink px-7 py-4 text-[15px] font-bold leading-none text-inverse-ink transition-colors hover:bg-inverse-ink hover:text-inverse">
                {{ __('cta.give') }}
              </a>
            @endif
          @endif
        </div>
      </div>

      @if ($variant === 'field' && $ways)
        {{-- Rule-separated rows, not three equal boxes. Corporate first, then
             church, then volunteer, then how to give — the same priority order
             the CTAs use, for the same reason. --}}
        <div class="ed-next__ways">
          @foreach ($ways as $way)
            <div class="ed-way">
              <h3 class="ed-name">{{ $way['heading'] }}</h3>
              <p class="ed-muted mt-1 max-w-[52ch] text-[0.98rem]">
                {{ $way['body'] }}
                @if (! empty($way['link']))
                  <a href="{{ $way['link']['href'] }}" class="ed-txtlink">{{ $way['link']['label'] }}</a>
                @endif
              </p>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</section>
