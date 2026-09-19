<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | The challenge ledger renders a stat as a bold opening clause (`label`) and
 | the sentence that finishes it (`body`). Optional: the stat band has no use
 | for it. See docs/data-contract.md § Stat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->json('body')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->dropColumn('body');
        });
    }
};
