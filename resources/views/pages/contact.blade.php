{{-- resources/views/pages/contact.blade.php --}}
{{-- Hero → enquiry form + office details. Spec §5 has no spine row for
     Contact; the page exists because "Partner with us" — the primary CTA on
     every other page — has to land somewhere a CSR department can actually
     use, which means a form and a named contact, not a donate button.

     The form markup is inline rather than a component: it is used once, and
     a <x-form.field> abstraction with one call site is not worth the file.
     Fields are full-width and labels wrap, because Indonesian labels run
     15-20% longer than their English equivalents. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('contact.hero.image_alt')))
@php($inputClass = 'w-full rounded border border-line bg-surface px-4 py-3 text-body text-ink
    focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2')

<x-layouts.site :title="__('contact.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('contact.hero.heading')"
    :subhead="__('contact.hero.body')"
    :image="$heroImage" />

  <section class="bg-raised py-14 md:py-24">
    <div class="mx-auto max-w-content px-4">
      <div class="grid gap-12 md:grid-cols-2">
        <div class="flex flex-col gap-6">
          <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ __('contact.form.heading') }}</h2>

          @if (session('contact.sent'))
            {{-- role=status announces the result to a screen reader without
                 stealing focus; the redirect already moved the reader here. --}}
            <p role="status" class="rounded border border-accent bg-surface p-4 text-body text-ink">
              {{ __('contact.form.sent') }}
            </p>
          @endif

          <form method="POST" action="{{ route(app()->getLocale().'.contact.send') }}" class="flex flex-col gap-5">
            @csrf

            {{-- Honeypot. A field no human sees and no screen reader reaches;
                 anything in it came from a bot, and the route rejects it.
                 Paired with a throttle on the route — see routes/web.php.
                 ponytail: honeypot + throttle only. Add a captcha if spam
                 actually gets through; do not add one pre-emptively. --}}
            <div class="hidden" aria-hidden="true">
              <label for="website">Website</label>
              <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>

            <div class="flex flex-col gap-2">
              <label for="name" class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ __('contact.form.name') }}</label>
              <input id="name" name="name" type="text" required
                     value="{{ old('name') }}"
                     @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                     class="{{ $inputClass }}">
              @error('name')<p id="name-error" class="text-[14px] font-semibold text-accent">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col gap-2">
              <label for="organisation" class="text-caption uppercase tracking-[0.08em] text-ink-muted">
                {{ __('contact.form.organisation') }} <span class="normal-case tracking-normal">({{ __('contact.form.optional') }})</span>
              </label>
              <input id="organisation" name="organisation" type="text"
                     value="{{ old('organisation') }}"
                     @error('organisation') aria-invalid="true" aria-describedby="organisation-error" @enderror
                     class="{{ $inputClass }}">
              @error('organisation')<p id="organisation-error" class="text-[14px] font-semibold text-accent">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col gap-2">
              <label for="email" class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ __('contact.form.email') }}</label>
              <input id="email" name="email" type="email" required autocomplete="email"
                     value="{{ old('email') }}"
                     @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                     class="{{ $inputClass }}">
              @error('email')<p id="email-error" class="text-[14px] font-semibold text-accent">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col gap-2">
              <label for="message" class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ __('contact.form.message') }}</label>
              <textarea id="message" name="message" rows="6" required
                        @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                        class="{{ $inputClass }}">{{ old('message') }}</textarea>
              @error('message')<p id="message-error" class="text-[14px] font-semibold text-accent">{{ $message }}</p>@enderror
            </div>

            <div>
              <x-button type="submit">{{ __('contact.form.submit') }}</x-button>
            </div>
          </form>
        </div>

        <div class="flex flex-col gap-6">
          <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ __('contact.find.heading') }}</h2>
          <dl class="grid gap-4">
            <div class="flex justify-between gap-6 border-b border-line pb-4">
              <dt class="text-ink-muted">{{ __('contact.find.email_key') }}</dt>
              <dd class="text-right font-semibold text-ink">{{ config('mail.contact_to') }}</dd>
            </div>
            <div class="flex justify-between gap-6 border-b border-line pb-4">
              <dt class="text-ink-muted">{{ __('contact.find.phone_key') }}</dt>
              <dd class="text-right font-semibold text-ink">{{ __('contact.find.placeholder') }}</dd>
            </div>
            <div class="flex justify-between gap-6 border-b border-line pb-4">
              <dt class="text-ink-muted">{{ __('contact.find.office_key') }}</dt>
              <dd class="text-right font-semibold text-ink">Waingapu, Sumba Timur</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </section>
</x-layouts.site>
