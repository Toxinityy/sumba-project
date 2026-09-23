<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Spec §9: "Minor subject records have no surname field at all. The schema
 | makes the rule unbreakable rather than merely documented."
 |
 | No code path ever wrote this column — MediaAssetFactory set it to null and
 | nothing else in app/, database/ or tests/ assigned it except the test that
 | proved the Eloquent guard rejected it. So there is no data to migrate and
 | nothing to preserve, and dropping it satisfies §9 exactly: no constraint,
 | no separate table, no dependency on the host's MySQL version.
 |
 | `posts` is the other half and it is not free — adults there legitimately
 | have surnames. See 2026_09_22_000002.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->dropColumn('subject_family_name');
        });
    }

    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('subject_family_name')->nullable();
        });
    }
};
