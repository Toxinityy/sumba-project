{{-- resources/views/components/site-footer.blade.php --}}
@php($locale = app()->getLocale())

{{--
  The footer is where the six pages folded out of the nav stay reachable.
  About, Children's Homes, Impact, Partners, Gallery and Projects all keep
  their routes and are all linked here, so nothing 404s and no existing link
  breaks — they just stop competing for attention in a ten-item menu.
--}}
<footer class="bg-inverse text-inverse-ink">
  <div class="mx-auto max-w-content px-4 pb-12 pt-20">
    <div class="mb-16 grid gap-12 md:grid-cols-3">
      <div class="flex flex-col gap-2">
        <p class="font-display text-[21px] font-semibold">Hope for Sumba</p>
        <p>{{ __('footer.tagline') }}</p>
      </div>
      <div class="flex flex-col gap-2">
        <p class="text-caption uppercase tracking-[0.08em] text-inverse-ink-muted">{{ __('footer.explore') }}</p>
        @foreach (['schools.index', 'stories.index', 'about', 'homes.index', 'impact', 'projects', 'partners', 'gallery.index'] as $name)
          <a href="{{ route("{$locale}.{$name}") }}" class="flex min-h-11 items-center hover:underline">{{ __('nav.' . $name) }}</a>
        @endforeach
      </div>
      <div class="flex flex-col gap-2">
        <p class="text-caption uppercase tracking-[0.08em] text-inverse-ink-muted">{{ __('footer.contact') }}</p>
        <a href="{{ route("{$locale}.give") }}" class="flex min-h-11 items-center hover:underline">{{ __('nav.give') }}</a>
        <a href="{{ \App\Support\LocalizedUrl::contact() }}" class="flex min-h-11 items-center hover:underline">{{ __('nav.contact') }}</a>
        <a href="{{ route("{$locale}.safeguarding") }}" class="flex min-h-11 items-center hover:underline">{{ __('nav.safeguarding') }}</a>
      </div>
    </div>

    {{-- The registration line is what a due-diligence reader looks for. --}}
    <div class="flex flex-col gap-2 border-t border-white/15 pt-6 text-[13.5px] text-inverse-ink-muted">
      <p>{{ __('footer.address') }}</p>
      <p>{{ __('footer.registration') }}</p>
    </div>
  </div>
</footer>
