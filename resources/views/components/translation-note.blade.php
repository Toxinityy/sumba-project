{{-- resources/views/components/translation-note.blade.php --}}
{{-- Spec §7: a missing translation renders the source language with a quiet
     inline note, and the note is itself translated — so the string comes from
     lang/*.json rather than being written here in one language.

     `from` is the locale the content actually resolved to (the view models'
     `translated_from` key, derived from HasTranslations::translationLocale()).
     Null means the reader is getting their own locale and there is nothing to
     say. Null is also what an empty-in-every-locale field produces, which is
     a blank section rather than a fallback — not this component's problem.

     Quiet, per §7: muted and italic, not a warning banner. No fixed width and
     no fixed height — the Indonesian sentence is the longer one and must be
     free to wrap to two lines at 360px (rule 2). --}}
@props(['from' => null])

@if ($from)
  <p role="note" class="max-w-prose text-[14px] italic leading-relaxed text-ink-muted">
    {{ __('translation.fallback.'.$from) }}
  </p>
@endif
