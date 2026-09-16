{{-- resources/views/components/site-nav.blade.php --}}
@php($locale = app()->getLocale())

<header class="sticky top-0 z-50 border-b border-line bg-surface">
  <div class="mx-auto flex max-w-content flex-wrap items-center gap-6 px-4 py-4">
    <a href="{{ route("{$locale}.home") }}"
       class="mr-auto flex min-h-11 items-center font-display text-[21px] font-semibold leading-tight text-ink">
      Hope for Sumba
    </a>

    {{-- No fixed widths: Indonesian labels run 15-20% longer than English and
         must not be clipped or forced to wrap mid-word. --}}
    <nav class="flex flex-wrap items-center gap-4" aria-label="{{ __('nav.label') }}">
      @foreach (['about', 'schools.index', 'homes.index', 'stories.index', 'give', 'contact'] as $name)
        <a href="{{ route("{$locale}.{$name}") }}"
           class="flex min-h-11 items-center px-2 text-[15px] font-semibold text-ink-muted hover:text-accent"
           @if (request()->routeIs("{$locale}.{$name}")) aria-current="page" @endif>
          {{ __('nav.' . $name) }}
        </a>
      @endforeach
    </nav>

    <x-language-switcher />
  </div>
</header>
