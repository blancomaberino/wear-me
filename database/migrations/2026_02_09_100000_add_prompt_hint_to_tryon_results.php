<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tryon_results', function (Blueprint $table) {
            $table->string('prompt_hint')->nullable()->after('is_favorite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tryon_results', function (Blueprint $table) {
            $table->dropColumn('prompt_hint');
        });
    }
};
