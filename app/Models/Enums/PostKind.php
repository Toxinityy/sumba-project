<?php

namespace App\Models\Enums;

enum PostKind: string
{
    case Profile = 'profile';
    case Update = 'update';
    case News = 'news';

    /** Profiles are portraits (4:5); updates and news are date-led (3:2). */
    public function aspectRatio(): string
    {
        return $this === self::Profile ? '4:5' : '3:2';
    }
}
