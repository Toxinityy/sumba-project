<?php

namespace App\Models\Enums;

enum PostKind: string
{
    case Profile = 'profile';
    case Update = 'update';
    case News = 'news';
    // Photo essays are posts whose body is mostly image blocks (spec §6:
    // "Gallery needs no model"). They have no subject and no page of their
    // own; the gallery is where they are read.
    case PhotoEssay = 'photo-essay';

    /** Profiles are portraits (4:5); updates and news are date-led (3:2). */
    public function aspectRatio(): string
    {
        return $this === self::Profile ? '4:5' : '3:2';
    }
}
