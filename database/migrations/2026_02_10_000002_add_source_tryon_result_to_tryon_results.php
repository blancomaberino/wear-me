<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tryon_results', function (Blueprint $table) {
            $table->foreignId('source_tryon_result_id')
                ->nullable()
                ->after('model_image_id')
                ->constrained('tryon_results')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tryon_results', function (Blueprint $table) {
            $table->dropForeign(['source_tryon_result_id']);
            $table->dropColumn('source_tryon_result_id');
        });
    }
};
