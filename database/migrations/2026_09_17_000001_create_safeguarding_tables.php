<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Consent and media come first because everything else hangs off them: an
 | image is the unit the site publishes, and consent is what makes publishing
 | it permissible. See spec §9.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();

            // There is no surname column, deliberately and permanently (§9).
            // Most consent subjects are minors, and a field that does not
            // exist cannot leak. Adults named in full are named on the Post,
            // which is a public editorial record rather than a child's file.
            $table->string('subject_given_name');
            $table->boolean('subject_is_minor')->default(true);

            // Guardian consent plus the child's own assent. Nullable because
            // an adult subject has no guardian; the model refuses to save a
            // minor's record without one.
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->boolean('subject_assented')->default(false);

            $table->string('scope');
            $table->date('granted_on');

            // Required, not optional: a story about a nine-year-old is still
            // indexed when they are nineteen, so every record expires and the
            // dashboard can list what is coming up.
            $table->date('review_on');

            $table->string('form_scan_path');
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->index(['scope', 'review_on']);
        });

        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('alt');
            $table->json('caption')->nullable();
            $table->string('credit')->nullable();

            // Evidence pairs are dated captions (§6); the date is a column so
            // "before" and "after" can be ordered and checked, not just read.
            $table->date('taken_on')->nullable();

            $table->boolean('depicts_minor')->default(false);
            $table->foreignId('consent_id')->nullable()->constrained()->restrictOnDelete();

            $table->string('subject_given_name')->nullable();
            $table->string('subject_family_name')->nullable();

            // Focal point so automatic cropping never takes someone's head
            // off, plus the named crops (wide/tall/card/story) as chosen
            // rectangles. Both are 0–1 fractions, resolution-independent.
            $table->float('focal_x')->default(0.5);
            $table->float('focal_y')->default(0.5);
            $table->json('crops')->nullable();

            $table->nullableMorphs('attachable');
            $table->string('role')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('consents');
    }
};
