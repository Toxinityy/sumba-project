<?php

namespace App\ViewModels;

use App\Models\Enums\PostKind;
use App\Models\Post;

/**
 * The stories' data, from App\Models\Post (docs/data-contract.md § Post).
 * ::recent() and ::find() back the stories rail, the stories index and the
 * story detail page; ::photoEssays() backs the Gallery.
 *
 * This was a fixture until 2026-09-20. The model produces the same arrays,
 * so no Blade template changed; the copy it held lives in
 * database/seeders/PostSeeder.php.
 *
 * `href` is added here rather than by the model, because it is the page that
 * knows where a kind leads: a profile has its own story page, a photo essay
 * has none and points at the Gallery (contract § Post).
 */
class PostData
{
    /** @return array<int, array> The newest stories, newest first. */
    public static function recent(int $n): array
    {
        return self::stories()->take($n)
            ->map(fn (Post $post) => self::withHref($post->toDetailArray(), $post))
            ->values()->all();
    }

    /** One post (story or photo essay) by slug, or null. */
    public static function find(string $slug): ?array
    {
        $post = Post::whereSlug($slug)->published()->first();

        return $post === null ? null : self::detail($post);
    }

    /** Full shape for a post the route has already bound. */
    public static function detail(Post $post): array
    {
        return self::withHref(
            $post->kind === PostKind::PhotoEssay ? $post->toCardArray() : $post->toDetailArray(),
            $post,
        ) + ['translated_from' => self::fallbackLocale($post)];
    }

    /**
     * The locale the story's prose actually resolved to, or null when that is
     * already the reader's own — which is what <x-translation-note> reads to
     * decide whether §7's quiet note belongs on the page. `body` is the
     * field that decides it: it is the bulk of what a reader came for, and a
     * translated title over an untranslated body is still a fallback.
     *
     * Null when the field is empty in every locale too. That is a blank
     * section, not a fallback, and claiming a translation exists would be
     * worse than saying nothing.
     *
     * Cards do not get this: a rail of stories is no place for the note.
     */
    private static function fallbackLocale(Post $post): ?string
    {
        $resolved = $post->translationLocale('body');

        return $resolved === app()->getLocale() ? null : $resolved;
    }

    /** @return array<int, array> Photo-essay posts, for the Gallery page. */
    public static function photoEssays(): array
    {
        return Post::published()->ofKind(PostKind::PhotoEssay)
            ->orderBy('published_at')->get()
            ->map(fn (Post $post) => self::withHref($post->toCardArray(), $post))
            ->all();
    }

    /**
     * A story is a profile with a pull quote. The profile posts that back a
     * school's People section have no quote and no body of their own, so they
     * are portraits on that school's page, not stories in the index.
     */
    private static function stories()
    {
        return Post::published()->ofKind(PostKind::Profile)
            ->whereNotNull('quote')
            ->orderByDesc('published_at')->get();
    }

    private static function withHref(array $shape, Post $post): array
    {
        $locale = app()->getLocale();

        return ['href' => $post->kind === PostKind::PhotoEssay
            ? route("{$locale}.gallery.index")
            : route("{$locale}.stories.show", $shape['slug'])] + $shape;
    }
}
