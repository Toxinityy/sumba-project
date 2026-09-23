<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
 | An adult subject's family name, kept out of `posts` so that a minor's
 | record has no surname field at all (spec §9).
 |
 | `subject_is_minor` is not editable and is always false: it exists so the
 | composite foreign key can point at posts(id, subject_is_minor) and make
 | "a surname on a minor" unrepresentable rather than merely refused.
 */
#[Fillable(['family_name'])]
class SubjectSurname extends Model
{
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
