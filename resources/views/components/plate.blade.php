{{-- resources/views/components/plate.blade.php --}}
@props(['variant' => 'field', 'ratio' => null, 'caption' => null])

{{--
  A captioned placeholder plate, replacing the grey box (and the placehold.co
  request behind <x-picture>'s fixture images) on the landing page.

  Built from palette tokens only — see resources/css/landing.css §3 — so it
  themes correctly, fetches nothing, and costs no layout shift. Three tonal
  variants (field / grass / dusk) carry colour across a page with zero
  photographs in it.

  The caption names the photograph that belongs in this frame, so a reviewer
  knows what to shoot. It sits on a solid --inverse-surface ground rather than
  on the gradient, which fixes its contrast at 13.96:1 light / 11.53:1 dark
  whatever the fill underneath is doing.

  MIGRATION PATH, deliberate: when real photography lands, a plate takes a
  `background-image` and drops its ::before and its caption. Aspect ratio,
  bleed, overlap and caption geometry all live in the frame, not the fill, so
  nothing on the page moves when the photographs arrive.

  aria-hidden: the plate is decoration standing in for a photograph that does
  not exist yet. Announcing "Photograph: children walking to school" to a
  screen-reader user would describe an image that is not there.
--}}
<div {{ $attributes->class(['plate', 'plate--' . $variant]) }}
     @if ($ratio) style="aspect-ratio: {{ $ratio }}" @endif
     aria-hidden="true">
  @if ($caption)
    <p class="plate__slug">{{ $caption }}</p>
  @endif
</div>
