{{-- resources/views/components/sections/partners.blade.php --}}
@props(['partners', 'heading' => null])

<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($heading)
      <h2 class="mb-12 font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
    @endif

    {{-- Plain <img>, not <x-picture>: partner logos are flat graphics, not
         photographs, and won't have been through the variant pipeline.
         <x-picture> throws without a jpeg srcset. --}}
    <ul class="flex flex-wrap items-center gap-12">
      @foreach ($partners as $partner)
        <li>
          <img src="{{ $partner['logo'] }}"
               alt="{{ $partner['name'] }}"
               width="160" height="60"
               loading="lazy"
               class="h-12 w-auto">
        </li>
      @endforeach
    </ul>
  </div>
</section>
