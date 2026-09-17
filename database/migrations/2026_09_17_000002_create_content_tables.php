<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Translated fields are JSON keyed by locale, including slugs (§7). There is
 | deliberately no funding column anywhere in this file — no goal, no total
 | raised, no percentage. `status` on a school is a short qualitative sentence
 | an editor writes ("Butuh 4 mitra lagi"), never a number.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->json('slug');
            $table->json('name');
            $table->string('level');
            $table->json('location');
            $table->json('lede')->nullable();
            $table->json('current_need')->nullable();
            $table->json('status')->nullable();

            // Counts for the facts list. The view model renders them as
            // strings beside non-numeric facts like "Gratis", so nothing
            // downstream depends on their being numbers.
            $table->unsignedInteger('pupils')->nullable();
            $table->unsignedInteger('teachers')->nullable();
            $table->unsignedSmallInteger('opened_year')->nullable();

            // The Context section describes circumstance and system, never
            // attributes of the children — see the rule on the template.
            $table->json('context_heading')->nullable();
            $table->json('context_body')->nullable();
            $table->json('work_heading')->nullable();
            $table->json('work_body')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('homes', function (Blueprint $table) {
            $table->id();
            $table->json('slug');
            $table->json('name');
            $table->json('location');
            $table->json('lede')->nullable();
            $table->json('care_model')->nullable();
            $table->json('current_need')->nullable();
            $table->json('status')->nullable();
            $table->unsignedInteger('residents')->nullable();
            $table->unsignedInteger('carers')->nullable();
            $table->json('context_heading')->nullable();
            $table->json('context_body')->nullable();
            $table->json('work_heading')->nullable();
            $table->json('work_body')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->json('slug');
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->string('status');

            // A project belongs to at most one school or home, and may belong
            // to neither (ministry-wide work).
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('home_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->json('slug');
            $table->json('title');
            $table->string('kind');
            $table->json('hook')->nullable();
            $table->json('body')->nullable();

            // Subject fields, optional and split on purpose: a post whose
            // subject is a minor may carry a given name only. The model
            // refuses to save a family name alongside subject_is_minor, which
            // is the closest a single table gets to §9's "no surname field at
            // all" without a per-driver CHECK constraint the schema builder
            // cannot express.
            $table->string('subject_given_name')->nullable();
            $table->string('subject_family_name')->nullable();
            $table->boolean('subject_is_minor')->default(false);
            $table->json('subject_role')->nullable();

            // Optional relation to a School, Home or Project so a post
            // surfaces on that entity's page automatically.
            $table->nullableMorphs('about');

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['kind', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('homes');
        Schema::dropIfExists('schools');
    }
};
