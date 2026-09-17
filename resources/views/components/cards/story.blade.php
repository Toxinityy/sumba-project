{{-- resources/views/components/cards/story.blade.php --}}
@props(['href', 'name', 'hook', 'image'])

<a href="{{ $href }}"
   class="flex flex-col overflow-hidden rounded-lg border border-line bg-raised transition-colors hover:border-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
  <div class="aspect-[4/5] overflow-hidden">
    <x-picture
      :sources="$image['sources']"
      :width="$image['width']"
      :height="$image['height']"
      :alt="$image['alt']"
      sizes="(max-width: 640px) 100vw, 33vw"
      class="h-full w-full object-cover" />
  </div>

  <div class="flex flex-1 flex-col gap-2 p-6">
    {{--
      Safeguarding: children are named by FIRST NAME ONLY, never a
      surname, and never a first name combined with a specific village
      plus a daily routine. Adults (teachers, community members, the
      founder) may be named in full with their role. The content model
      enforces this by giving minors no surname field at all — do not
      undo that protection here by concatenating one onto $name.
    --}}
    <p class="font-display text-[22px] leading-tight text-ink">{{ $name }}</p>
    <p class="text-[15px] leading-relaxed text-ink-muted">{{ $hook }}</p>
  </div>
</a>
