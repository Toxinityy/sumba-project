<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/*
 | Translated fields are a JSON column keyed by locale — ['id' => …, 'en' => …].
 | No translation package: two locales, a handful of models, and a JSON column
 | that MySQL and SQLite both index-free query well enough at this size.
 |
 | A model lists its translated fields in $translatable; they are cast to array
 | automatically from here so each model states the fact once.
 */
trait HasTranslations
{
    public function initializeHasTranslations(): void
    {
        $this->mergeCasts(array_fill_keys($this->translatable, 'array'));
    }

    /**
     * The locale a field will actually render in, or null if it is empty in
     * every locale. Pages need this to decide whether to show the fallback
     * note (§7) — a missing translation renders the source language with a
     * quiet note, it never 404s and never renders blank.
     */
    public function translationLocale(string $field, ?string $locale = null): ?string
    {
        $values = $this->getAttribute($field) ?? [];

        foreach ([$locale ?? app()->getLocale(), config('locales.default')] as $candidate) {
            if (filled($values[$candidate] ?? null)) {
                return $candidate;
            }
        }

        return null;
    }

    public function trans(string $field, ?string $locale = null): ?string
    {
        $resolved = $this->translationLocale($field, $locale);

        return $resolved === null ? null : $this->getAttribute($field)[$resolved];
    }

    /**
     * Slugs are per-locale (§7), so a lookup is always scoped to one locale —
     * /id/sekolah/karuni and /en/schools/karuni may carry different slugs and
     * matching the wrong one would serve the wrong URL under the right prefix.
     */
    public function scopeWhereSlug(Builder $query, string $slug, ?string $locale = null): void
    {
        $locale ??= app()->getLocale();

        $query->where(function (Builder $query) use ($slug, $locale) {
            $query->where('slug->'.$locale, $slug);

            if ($locale !== config('locales.default')) {
                $query->orWhere(function (Builder $fallback) use ($slug, $locale) {
                    $fallback->where('slug->'.config('locales.default'), $slug)
                        ->where(function (Builder $missing) use ($locale) {
                            $missing->whereNull('slug->'.$locale)->orWhere('slug->'.$locale, '');
                        });
                });
            }
        });
    }
}
