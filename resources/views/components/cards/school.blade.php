{{-- resources/views/components/cards/school.blade.php --}}
@props(['href', 'level', 'name', 'location', 'need', 'status', 'image', 'ageRange' => null])

<a href="{{ $href }}"
   class="flex flex-col overflow-hidden rounded-lg border border-line bg-raised transition-colors hover:border-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
  <div class="relative">
    <div class="aspect-[4/5] overflow-hidden">
      <x-picture
        :sources="$image['sources']"
        :width="$image['width']"
        :height="$image['height']"
        :alt="$image['alt']"
        sizes="(max-width: 640px) 100vw, 33vw"
        class="h-full w-full object-cover" />
    </div>
  </div>

  <div class="flex flex-1 flex-col gap-2 p-6">
    {{-- The level ladder replaces the single "SMP" pill this card used to
         carry. A lone pill tells an overseas reader nothing; the ladder shows
         all three rungs and lights this school's, which turns the repetition
         down a directory into a scannable index. See
         resources/views/components/level-ladder.blade.php. --}}
    <x-level-ladder :level="$level" :ageRange="$ageRange" class="mb-2" />

    <p class="text-[14px] font-semibold text-ink-muted">{{ $location }}</p>
    <p class="font-display text-[22px] leading-tight text-ink">{{ $name }}</p>
    <p class="text-[15px] leading-relaxed text-ink-muted">{{ $need }}</p>

    {{--
      Qualitative status only. No goal, no amount raised, no progress bar,
      no percentage. `status` is a short qualitative string an editor sets
      by hand ("Butuh 4 mitra lagi", "Didanai penuh tahun ini"). A bar
      frozen at 40% for six months costs more credibility with an
      institutional donor than the precision earns, and this team cannot
      keep such numbers current. Do not add a <progress> element, a
      role="progressbar", or a numeric percentage here.
      No truncation/no-wrap: Indonesian status text runs 15-20% longer
      than English and must wrap.
    --}}
    <p class="mt-auto pt-2 text-[14px] font-bold text-accent">{{ $status }}</p>
  </div>
</a>
