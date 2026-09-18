{{-- resources/views/pages/story.blade.php --}}
{{-- Spine per design spec §5: Hero → Lede → Quote → Next step. $post comes
     from App\ViewModels\PostData::find() via routes/web.php.

     Safeguarding: 'name' is first-name-only for minor subjects by
     construction (see PostData's header comment) — never concatenate a
     surname here, and never pair the subject's name with a specific
     village plus a daily routine in the same sentence (the lede/body
     content already respects this; do not add new copy that breaks it). --}}
<x-layouts.site :title="$post['title'].' — Hope for Sumba'">
  <x-sections.hero
    :heading="$post['title']"
    :subhead="$post['name']"
    :image="$post['image']" />

  <x-sections.lede :label="__('stories.hero.label')" surface="raised">
    {!! $post['body'] !!}
  </x-sections.lede>

  <x-sections.quote :attribution="$post['quote']['attribution']" :role="$post['quote']['role']">
    {{ $post['quote']['text'] }}
  </x-sections.quote>

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
