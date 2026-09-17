{{-- resources/views/components/sections/form.blade.php --}}
@props(['fields', 'submitLabel', 'label' => null, 'heading' => null])

{{-- The fifteenth section (spec §5). Renders labelled fields and a submit
     button — nothing else. It has no baked-in action, method or spam guard:
     the caller wires those through $attributes and the default slot, because
     submission handling belongs to the page (or, later, Agent B's data
     layer), not to a design-system component.

     "$fields" real name/for="" labels, never a placeholder standing in for
     one — placeholder-as-label disappears the moment someone types and never
     reaches a screen reader as a label at all.

     Not wired to anything by itself: a caller that renders this with no
     method/action gets an inert form (a GET reload on submit, nothing sent,
     no success faked). Contact wires it to a real POST route in
     routes/web.php — see resources/views/pages/contact.blade.php. --}}
<section class="bg-raised py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="flex max-w-prose flex-col gap-6">
      @if ($label)
        <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
      @endif
      @if ($heading)
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
      @endif
    </div>

    <form {{ $attributes->merge(['class' => 'mt-8 flex max-w-prose flex-col gap-5']) }}>
      @csrf

      {{-- Room for a caller's honeypot field, a success banner, or anything
           else that has to live inside the <form> element. Empty by default. --}}
      {{ $slot }}

      @foreach ($fields as $field)
        @php($name = $field['name'])
        @php($type = $field['type'] ?? 'text')
        {{-- $errors is normally shared by the session middleware on every
             request; guarded here so this component also renders standalone
             (the gallery, a component test) with no session in play. --}}
        @php($error = isset($errors) ? $errors->first($name) : null)

        <div class="flex flex-col gap-2">
          {{-- A real <label for> bound by id — never a placeholder doing
               duty as one. No fixed width, no truncate, no whitespace-nowrap:
               the Indonesian label runs 15-20% longer than English. --}}
          <label for="{{ $name }}" class="text-caption uppercase tracking-[0.08em] text-ink-muted">
            {{ $field['label'] }}
          </label>

          @if ($type === 'textarea')
            <textarea
              id="{{ $name }}" name="{{ $name }}" rows="{{ $field['rows'] ?? 5 }}"
              @if ($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
              class="min-h-11 w-full rounded border border-line bg-surface px-4 py-3 text-body text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
            >{{ old($name) }}</textarea>
          @else
            <input
              id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name) }}"
              @if ($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
              class="min-h-11 w-full rounded border border-line bg-surface px-4 py-3 text-body text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
          @endif

          @if ($error)
            <p id="{{ $name }}-error" class="text-[14px] font-semibold text-accent">{{ $error }}</p>
          @endif
        </div>
      @endforeach

      <div>
        <x-button type="submit">{{ $submitLabel }}</x-button>
      </div>
    </form>
  </div>
</section>
