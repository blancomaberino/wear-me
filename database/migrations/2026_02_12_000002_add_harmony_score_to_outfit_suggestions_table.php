<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outfit_suggestions', function (Blueprint $table) {
            $table->unsignedTinyInteger('harmony_score')->nullable()->after('is_saved');
        });
    }

    public function down(): void
    {
        Schema::table('outfit_suggestions', function (Blueprint $table) {
            $table->dropColumn('harmony_score');
        });
    }
};
