{{-- resources/views/components/sections/context.blade.php --}}
@props(['label' => null, 'heading', 'image', 'reverse' => false])

{{--
  THE DIGNITY RULE — read this before writing copy for this section.

  This is the single place a nonprofit site most often destroys the dignity
  of the people it portrays, so the rule is spelled out here, not just in a
  spec someone read once.

  Describe the challenge as circumstance and system: distance to the nearest
  school, a teacher shortage, no grid electricity, a river that floods the
  only road for three months a year. Never describe it as an attribute of
  the children or their families. "The village has no library" is in scope.
  "These children are poor" is not — that sentence turns a person into a
  symptom, and it reads as amateur to the institutional donors this site is
  written for as much as it fails the people in the photo.

  The same test applies to the photograph, not just the words: illustrate
  with people acting — walking, building, teaching, reading — never people
  posed as suffering. If a caption or an image needs pity to make its point,
  find the circumstance behind it and describe that instead.
--}}
<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid items-center gap-12 md:grid-cols-2">
      <div @class(['flex max-w-prose flex-col gap-6', 'md:order-2' => $reverse])>
        @if ($label)
          <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
        @endif
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
        <div class="text-body text-ink">{{ $slot }}</div>
      </div>

      <div @class(['md:order-1' => $reverse])>
        <x-picture
          :sources="$image['sources']"
          :width="$image['width']"
          :height="$image['height']"
          :alt="$image['alt']"
          sizes="(max-width: 768px) 100vw, 50vw"
          class="rounded" />
      </div>
    </div>
  </div>
</section>
