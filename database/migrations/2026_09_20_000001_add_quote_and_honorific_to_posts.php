<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Two fields the pages have always rendered and the schema never had
 | (docs/data-contract.md § Post, amended 2026-09-20).
 |
 | `quote` is the pull quote a story detail page is built around — the route
 | 404s a post without one. Only the text is stored: its attribution and role
 | are the post's own subject fields, so a quote cannot name a child
 | differently from the post that carries it.
 |
 | `subject_honorific` keeps "Ibu" and "Bapak" out of the name fields. Without
 | it, a head teacher is either "Maria Bulu" everywhere, which drops a form of
 | respect the copy uses deliberately, or "Ibu Maria" gets typed into the
 | given-name field, which then reads wrong wherever a bare first name is
 | right. It is never set for a minor: §9's rule is that a child is a given
 | name alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('quote')->nullable()->after('body');
            $table->string('subject_honorific')->nullable()->after('kind');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['quote', 'subject_honorific']);
        });
    }
};
