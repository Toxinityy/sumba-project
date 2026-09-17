<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsorship_tiers', function (Blueprint $table) {
            $table->id();
            $table->json('title');

            // A per-tier price in whole rupiah — "a classroom costs this
            // much" — not a fundraising total. Nothing sums this column.
            $table->unsignedBigInteger('cost');

            $table->json('description');
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();

            // A partner's name is a proper noun and its logo a flat graphic
            // that never enters the variant pipeline, so neither is
            // translated and the logo is a plain path.
            $table->string('name');
            $table->string('logo_path');
            $table->string('type');

            $table->json('testimonial')->nullable();
            $table->string('testimonial_attribution')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->json('label');

            // A string, not an integer: "612" and "3 dari 4" render in the
            // same slot and the band displays whatever it is given.
            $table->string('value');

            // Undated statistics are what a due-diligence reader distrusts,
            // so the date is required and rendered wherever the stat appears.
            $table->date('as_of');

            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('sponsorship_tiers');
    }
};
