<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make garment_id nullable on tryon_results
        Schema::table('tryon_results', function (Blueprint $table) {
            $table->dropForeign(['garment_id']);
        });

        Schema::table('tryon_results', function (Blueprint $table) {
            $table->unsignedBigInteger('garment_id')->nullable()->change();
            $table->foreign('garment_id')->references('id')->on('garments')->nullOnDelete();
        });

        // Create pivot table
        Schema::create('tryon_result_garment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryon_result_id')->constrained('tryon_results')->cascadeOnDelete();
            $table->foreignId('garment_id')->constrained('garments')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tryon_result_id', 'garment_id']);
        });

        // Backfill pivot from existing garment_id
        DB::statement('
            INSERT INTO tryon_result_garment (tryon_result_id, garment_id, sort_order, created_at, updated_at)
            SELECT id, garment_id, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM tryon_results
            WHERE garment_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('tryon_result_garment');

        Schema::table('tryon_results', function (Blueprint $table) {
            $table->dropForeign(['garment_id']);
        });

        Schema::table('tryon_results', function (Blueprint $table) {
            $table->unsignedBigInteger('garment_id')->nullable(false)->change();
            $table->foreign('garment_id')->references('id')->on('garments')->cascadeOnDelete();
        });
    }
};
