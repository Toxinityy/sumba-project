{{-- resources/views/components/level-ladder.blade.php --}}
@props(['level', 'ageRange' => null])

{{--
  THE LEVEL LADDER — replaces the single level pill on every school.

  Why the whole ladder and not just this school's rung: TK / SMP / SMA is real,
  scannable information a foreign donor does not know. A lone pill reading
  "SMP" tells them nothing. Showing all three rungs every time and lighting
  the one this school is on turns repetition into a running index — the lit
  rung moves down the rail as you scan the list, so every SMA is findable by
  looking for the mark at the bottom.

  DIFFERENTIATION IS NEVER CONTRAST ALONE. The lit rung is Fraunces at
  `opsz 14 / wght 700`, 27px, in full --ink, with a 3px --accent rule in the
  left gutter. The unlit rungs are Plus Jakarta Sans at --ink-muted, which is
  6.85:1 on surface / 7.31:1 on raised / 6.24:1 on sunk — above AA, never
  greyed below the threshold to look "off". Optical size, weight and the rule
  do the work; see resources/css/landing.css §4 for the geometry.

  Accessibility: the unlit rungs are decorative repetition (the same three
  strings on every school in the list), so they are aria-hidden and the lit
  one carries a visually-hidden ", jenjang sekolah ini" / ", this school's
  level". A screen reader hears one level per school, not nine.
--}}
@php($levels = ['TK', 'SMP', 'SMA'])

<ul {{ $attributes->class('lvl') }} aria-label="{{ __('school.level_label') }}">
  @foreach ($levels as $rung)
    @if ($rung === $level)
      <li class="lvl__i is-on">{{ $rung }}<span class="sr-only">, {{ __('school.level_current') }}</span></li>
    @else
      <li class="lvl__i" aria-hidden="true">{{ $rung }}</li>
    @endif
  @endforeach

  {{-- No whitespace-nowrap: "4-6 tahun" is short but the English "ages 4-6"
       and any future longer phrasing must be free to wrap. --}}
  @if ($ageRange)
    <li class="lvl__age">{{ $ageRange }}</li>
  @endif
</ul>
