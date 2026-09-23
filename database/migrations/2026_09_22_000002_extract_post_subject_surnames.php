<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Spec §9: "Minor subject records have no surname field at all."
 |
 | Unlike media_assets, this column is populated — adults on this site
 | legitimately have surnames — so the rows move rather than being dropped.
 |
 | The invariant is a composite foreign key, not a CHECK constraint and not a
 | model event. subject_surnames.subject_is_minor is always false, and it
 | points at posts(id, subject_is_minor), so:
 |
 |   - a surname row for a minor post finds no parent  -> rejected
 |   - flipping a surnamed adult to minor orphans the child row -> rejected
 |
 | Both fail at the database, which a raw or bulk UPDATE cannot talk its way
 | past. See "Subject identity" in docs/data-contract.md for why this rather
 | than a CHECK: no host exists to prove MySQL would enforce one, and below
 | 8.0.16 it parses CHECK and silently ignores it.
 */
return new class extends Migration
{
    public function up(): void
    {
        // The parent key the child table points at. MySQL requires a unique
        // index on the referenced columns; SQLite requires it too.
        Schema::table('posts', function (Blueprint $table) {
            $table->unique(['id', 'subject_is_minor'], 'posts_id_minor_unique');
        });

        Schema::create('subject_surnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->unique();

            // Always false: this table describes adults only. It is a stored
            // column rather than a literal because no engine will reference a
            // constant in a foreign key.
            $table->boolean('subject_is_minor')->default(false);

            $table->string('family_name');
            $table->timestamps();

            $table->foreign(['post_id', 'subject_is_minor'])
                ->references(['id', 'subject_is_minor'])->on('posts')
                ->cascadeOnDelete();
        });

        // Move the existing rows before the column goes. Adults only: a minor
        // row carrying a surname is already a §9 violation and must not be
        // carried forward. Counted before writing this migration on the
        // seeded database — 0 such rows, 13 adult rows to move.
        DB::table('posts')
            ->whereNotNull('subject_family_name')
            ->where('subject_family_name', '!=', '')
            ->where('subject_is_minor', false)
            ->orderBy('id')
            ->each(fn ($post) => DB::table('subject_surnames')->insert([
                'post_id' => $post->id,
                'subject_is_minor' => false,
                'family_name' => $post->subject_family_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('subject_family_name');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('subject_family_name')->nullable();
        });

        DB::table('subject_surnames')->orderBy('id')->each(
            fn ($row) => DB::table('posts')->where('id', $row->post_id)
                ->update(['subject_family_name' => $row->family_name])
        );

        Schema::dropIfExists('subject_surnames');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique('posts_id_minor_unique');
        });
    }
};
