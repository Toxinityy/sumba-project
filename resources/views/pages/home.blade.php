{{-- resources/views/pages/home.blade.php --}}
{{--
  THE LANDING PAGE. It carries the whole narrative, so most visitors never have
  to click: hero → who the people are → the challenge → what the ministry does
  → scale → evidence → a human voice → featured schools → stories → partners →
  next step.

  That is why the nav dropped from ten items to four (Sekolah, Cerita, Dukung
  Kami, Kontak) and why About, Children's Homes, Impact, Partners, Gallery and
  Projects are folded into the sections below. Their routes are all still live
  and every one of them is linked from the section it belongs to and from the
  footer — nothing 404s and no existing link breaks. They simply stop
  competing for attention in a menu.

  GEOMETRY (spec §5, amended 2026-09-18). The first build gave all fifteen
  sections identical geometry and the result read as assembled rather than
  designed. The cause was the uniform geometry, NOT the fixed section set — so
  the set stays and the geometry varies. Every section below declares its own
  padding step, its own ground and, where it has one, its own variant. Read the
  `pad` values down the page and you are reading the page's rhythm:

    hero      bespoke (top only)   the plate hangs past its own lower edge
    people    pad-xl               a pivot, given air
    challenge pad-xl               a pivot, given air
    work      pad-xl               a pivot, given air
    scale     pad-open  ┐
    evidence  pad-mid   ├ ONE dark chapter, not three inverted bands
    voice     pad-close ┘          plus an extra floor for the overlap below
    schools   pad-cont             receives the overlap: no top padding at all
    stories   pad-xl
    partners  pad-m                deliberately the fastest section on the page
    next step pad-xl

  Data comes from App\ViewModels fixtures via routes/web.php; see
  docs/data-contract.md. Nothing here formats a number or resolves a locale.
--}}

{{-- The three portraits are ADULTS: staff and community members, named in full
     with their role, which is what the safeguarding rule permits. Children
     appear only in the stories section, by first name alone. The lang strings
     are "Name — Role" so the two read correctly in a sentence elsewhere; the
     band wants them apart. --}}
@php($person = function (string $key, string $altKey) {
    [$name, $role] = array_pad(explode(' — ', __($key), 2), 2, null);

    return ['name' => $name, 'role' => $role, 'alt' => __($altKey)];
})

<x-layouts.site title="Hope for Sumba">

  {{-- 1. HERO ------------------------------------------------------------ --}}
  <x-sections.hero
    variant="editorial"
    tone="surface"
    plate="grass"
    :heading="__('home.hero.heading')"
    :accent="__('home.hero.accent')"
    :subhead="__('home.hero.subhead')"
    :image="['alt' => __('home.hero.image_alt')]">
    <x-slot:actions>
      {{-- Partner first, give second. A CSR department cannot click Donate. --}}
      <x-button :href="route(app()->getLocale().'.contact')" variant="primary">{{ __('cta.partner') }}</x-button>
      <x-button :href="route(app()->getLocale().'.give')" variant="secondary">{{ __('cta.give') }}</x-button>
    </x-slot:actions>
  </x-sections.hero>

  {{-- 2. WHO THE PEOPLE ARE (folds in About) ----------------------------- --}}
  <x-sections.people
    variant="offset"
    pad="xl"
    tone="surface"
    plate="tonal"
    :label="__('home.lede.label')"
    :heading="__('home.people.heading')"
    :en="__('home.people.gloss')"
    :portraits="[
      $person('about.people.reynold', 'about.people.reynold_alt'),
      $person('about.people.maria', 'about.people.maria_alt'),
      $person('about.people.yohanis', 'about.people.yohanis_alt'),
    ]">
    <p>{{ __('home.people.body1') }}</p>
    <p class="mt-4">{{ __('home.people.body2') }}</p>
    <p class="mt-5">
      <a class="ed-txtlink" href="{{ route(app()->getLocale().'.about') }}">{{ __('home.people.link') }}</a>
    </p>
  </x-sections.people>

  {{-- 3. THE CHALLENGE --------------------------------------------------- --}}
  <x-sections.stat-band
    variant="ledger"
    pad="xl"
    tone="sunk"
    :label="__('home.challenge.label')"
    :heading="__('home.challenge.heading')"
    :en="__('home.challenge.gloss')"
    :stats="$challenge" />

  {{-- 4. WHAT THE MINISTRY DOES (folds in Children's Homes and Projects) -- --}}
  <x-sections.work
    variant="interlock"
    pad="xl"
    tone="surface"
    plate="field"
    plateNarrow="dusk"
    :heading="__('home.lede.heading')"
    :en="__('home.work.gloss')"
    :image="['alt' => __('home.work.image_alt')]"
    :imageNarrow="['alt' => __('home.work.image_alt_narrow')]">
    <p>{{ __('home.work.lead') }}</p>
    <x-slot:note>
      <p>{{ __('home.work.note') }}</p>
      <p class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
        <a class="ed-txtlink" href="{{ route(app()->getLocale().'.projects') }}">{{ __('home.work.link') }}</a>
        <a class="ed-txtlink" href="{{ route(app()->getLocale().'.homes.index') }}">{{ __('home.work.homes_link') }}</a>
      </p>
    </x-slot:note>
  </x-sections.work>

  {{-- 5-7. THE DARK CHAPTER ---------------------------------------------- --}}
  {{-- Three sections, one continuous inverse field. Scale opens it, evidence
       sits inside it with no padding of its own, and the voice closes it with
       an extra floor so the card that overlaps up from section 8 can never
       reach the pull quote. The reader passes through one chapter rather than
       three alternating bands. --}}
  <x-sections.stat-band variant="scale" pad="open" tone="chapter" :stats="$stats">
    <x-slot:more>
      <a class="ed-txtlink" href="{{ route(app()->getLocale().'.impact') }}">{{ __('home.evidence.link') }}</a>
    </x-slot:more>
  </x-sections.stat-band>

  <x-sections.evidence
    variant="offset"
    pad="mid"
    tone="chapter"
    plate="pair"
    :label="__('home.evidence.label')"
    :heading="__('home.evidence.heading')"
    :en="__('home.evidence.gloss')"
    :before="['alt' => __('home.evidence.before_alt'), 'caption' => __('home.evidence.before_caption')]"
    :after="['alt' => __('home.evidence.after_alt'), 'caption' => __('home.evidence.after_caption')]" />

  <x-sections.quote
    variant="voice"
    pad="close"
    tone="chapter"
    :attribution="$voice['attribution']"
    :role="$voice['role']">
    {{ $voice['text'] }}
  </x-sections.quote>

  {{-- 8. FEATURED SCHOOLS ------------------------------------------------ --}}
  <x-sections.directory
    variant="rail"
    pad="cont"
    tone="surface"
    plate="tonal"
    :featured="$featuredSchool"
    :schools="$schools"
    :label="__('home.featured.label')"
    :heading="__('home.schools.heading')"
    :en="__('home.schools.gloss')">
    <x-slot:more>
      <a class="ed-txtlink" href="{{ route(app()->getLocale().'.schools.index') }}">{{ __('home.schools.more') }}</a>
    </x-slot:more>
  </x-sections.directory>

  {{-- 9. STORIES (folds in Gallery) -------------------------------------- --}}
  <x-sections.stories
    variant="stagger"
    pad="xl"
    tone="raised"
    plate="tonal"
    :label="__('home.stories.label')"
    :heading="__('home.stories.heading')"
    :en="__('home.stories.gloss')"
    :stories="$stories">
    <x-slot:more>
      <span class="flex flex-wrap gap-x-6 gap-y-2">
        <a class="ed-txtlink" href="{{ route(app()->getLocale().'.stories.index') }}">{{ __('home.stories.more') }}</a>
        <a class="ed-txtlink" href="{{ route(app()->getLocale().'.gallery.index') }}">{{ __('home.stories.gallery_link') }}</a>
      </span>
    </x-slot:more>
  </x-sections.stories>

  {{-- 10. PARTNERS (folds in the Partners page) -------------------------- --}}
  <x-sections.partners
    variant="marks"
    pad="m"
    tone="surface"
    :partners="$partners"
    :heading="__('home.partners.heading')">
    <p>{{ __('home.partners.note') }}</p>
    <p class="mt-3">
      <a class="ed-txtlink" href="{{ route(app()->getLocale().'.partners') }}">{{ __('home.partners.link') }}</a>
    </p>
  </x-sections.partners>

  {{-- 11. NEXT STEP ----------------------------------------------------- --}}
  {{-- A large tinted field, not a second dark band. Accent is never used as
       TEXT on this ground (3.92:1); the primary button uses accent as a
       background with accent-ink on it, and every link here is badge-ink. --}}
  <x-sections.next-step
    variant="field"
    pad="xl"
    tone="tint"
    :heading="__('nextstep.heading')"
    :en="__('home.next.gloss')"
    :body="__('nextstep.body')"
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')"
    :ways="[
      ['heading' => __('give.corporate.heading'), 'body' => __('give.corporate.body')],
      ['heading' => __('give.church.heading'), 'body' => __('give.church.body')],
      ['heading' => __('give.volunteer.heading'), 'body' => __('give.volunteer.body')],
      ['heading' => __('give.how.heading'), 'body' => __('home.next.how_body'), 'link' => [
        'href' => route(app()->getLocale().'.give'), 'label' => __('home.next.how_link'),
      ]],
    ]" />
</x-layouts.site>
