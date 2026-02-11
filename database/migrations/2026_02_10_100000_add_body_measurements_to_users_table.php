<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('measurement_unit', 10)->default('metric');
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->decimal('chest_cm', 5, 1)->nullable();
            $table->decimal('waist_cm', 5, 1)->nullable();
            $table->decimal('hips_cm', 5, 1)->nullable();
            $table->decimal('inseam_cm', 5, 1)->nullable();
            $table->decimal('shoe_size_eu', 4, 1)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'measurement_unit', 'height_cm', 'weight_kg', 'chest_cm',
                'waist_cm', 'hips_cm', 'inseam_cm', 'shoe_size_eu',
            ]);
        });
    }
};
