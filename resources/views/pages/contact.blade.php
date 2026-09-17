{{-- resources/views/pages/contact.blade.php --}}
{{-- Spine per design spec §5: Hero → Form (partnership enquiry) →
     Detail panel (where to find us) → Next step.

     "Partner with us" is the site's primary CTA (spec §5's "Two hard
     rules"): a CSR department cannot click Donate, it needs a proposal, a
     budget line and a named contact, and this page is where that
     conversation starts — so the form here has to actually work.

     <x-sections.form> (spec §5's fifteenth section) carries no baked-in
     action of its own; method/action come through on the tag below, wired
     to the real POST route in routes/web.php (Mail::raw, honeypot,
     throttle:5,1 — built in an earlier pass, still the delivery mechanism).
     That route already sends real mail and is covered by
     tests/Feature/Pages/ContactPageTest.php, so nothing here is a fake
     success page — the CLAUDE.md/brief note about not faking submission
     describes the section component in isolation (e.g. if it were dropped
     into the gallery with no route behind it), not this page, which has had
     working delivery since Pass 1. Flagged in
     docs/agent-a-pages-report.md rather than silently decided either way. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('contact.hero.image_alt')))
@php($facts = [
    ['key' => __('contact.find.email_key'), 'value' => config('mail.contact_to')],
    ['key' => __('contact.find.phone_key'), 'value' => __('contact.find.placeholder')],
    ['key' => __('contact.find.office_key'), 'value' => 'Waingapu, Sumba Timur'],
])

<x-layouts.site :title="__('contact.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('contact.hero.heading')"
    :subhead="__('contact.hero.body')"
    :image="$heroImage" />

  <x-sections.form
    method="POST"
    :action="route(app()->getLocale().'.contact.send')"
    :label="__('contact.hero.label')"
    :heading="__('contact.form.heading')"
    :fields="[
        ['name' => 'name', 'label' => __('contact.form.name'), 'type' => 'text'],
        ['name' => 'organisation', 'label' => __('contact.form.organisation').' ('.__('contact.form.optional').')', 'type' => 'text'],
        ['name' => 'email', 'label' => __('contact.form.email'), 'type' => 'email'],
        ['name' => 'message', 'label' => __('contact.form.message'), 'type' => 'textarea', 'rows' => 6],
    ]"
    :submitLabel="__('contact.form.submit')">

    @if (session('contact.sent'))
      {{-- role=status announces the result to a screen reader without
           stealing focus; the redirect already moved the reader here. --}}
      <p role="status" class="rounded border border-accent bg-surface p-4 text-body text-ink">
        {{ __('contact.form.sent') }}
      </p>
    @endif

    {{-- Honeypot. A field no human sees and no screen reader reaches;
         anything filled in came from a bot, and the route rejects it.
         Paired with throttle:5,1 on the route — see routes/web.php.
         ponytail: honeypot + throttle only. Add a captcha if spam
         actually gets through; do not add one pre-emptively. --}}
    <div class="hidden" aria-hidden="true">
      <label for="website">Website</label>
      <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
    </div>
  </x-sections.form>

  <x-sections.current-need
    :heading="__('contact.find.heading')"
    :status="__('contact.find.status')"
    :facts="$facts">
    <p>{{ __('contact.hero.body') }}</p>
  </x-sections.current-need>

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
