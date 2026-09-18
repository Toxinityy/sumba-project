{{-- resources/views/components/site-nav.blade.php --}}
@php($locale = app()->getLocale())

{{--
  FOUR ITEMS, down from ten.

  The landing page now carries the whole narrative, so About, Children's Homes,
  Impact, Partners, Gallery and Projects stop competing for attention in the
  menu — they are folded into landing sections and linked from those sections
  and from the footer. Their routes stay alive and nothing 404s; they are
  simply not the four things a first-time visitor has to choose between.

  What is left is the four destinations that are genuinely destinations:
  the schools directory, the stories index, how to get involved, and contact.

  Opaque, not frosted: a translucent bar sitting over the landing page's dark
  chapter darkened its own ground enough to pull the accent wordmark to 3.86:1.
  An opaque bar holds 4.68:1 at every scroll position.
--}}
<header class="sticky top-0 z-50 border-b border-line bg-surface">
  {{-- The same 1440px band and the same fluid gutter as the hero below it, so
       the wordmark sits on the masthead's left edge rather than 40px inside
       it. --}}
  <div class="ed-wrap ed-wrap--wide flex flex-wrap items-center gap-x-6 gap-y-2 py-3">
    {{-- The label is ONE flex item, not two: a bare text node beside a <span>
         inside a flex container loses the whitespace between them, and the
         wordmark renders as "Hope forSumba". --}}
    <a href="{{ route("{$locale}.home") }}"
       class="mr-auto flex min-h-11 items-center text-ink">
      <span class="ed-brand">Hope for <span>Sumba</span></span>
    </a>

    {{-- No fixed widths: Indonesian labels run 15-20% longer than English and
         must not be clipped or forced to wrap mid-word. --}}
    <nav class="flex flex-wrap items-center gap-x-4 gap-y-1" aria-label="{{ __('nav.label') }}">
      @foreach (['schools.index', 'stories.index', 'give', 'contact'] as $name)
        <a href="{{ route("{$locale}.{$name}") }}"
           class="flex min-h-11 items-center px-1 text-[15px] font-semibold text-ink-muted hover:text-ink"
           @if (request()->routeIs("{$locale}.{$name}")) aria-current="page" @endif>
          {{ __('nav.' . $name) }}
        </a>
      @endforeach
    </nav>

    <div class="flex items-center gap-3">
      <x-language-switcher />
      {{-- The primary CTA, present from the first screen on wide viewports
           only: below that the four nav items plus the language switcher
           already fill the bar, and the hero carries the same action. --}}
      <a href="{{ route("{$locale}.contact") }}"
         class="hidden min-h-11 items-center justify-center rounded-pill bg-accent px-4 text-[14px] font-bold text-accent-ink hover:bg-accent-strong xl:inline-flex">
        {{ __('cta.partner') }}
      </a>
    </div>
  </div>
</header>
