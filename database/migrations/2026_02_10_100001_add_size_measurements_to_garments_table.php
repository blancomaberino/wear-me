<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('garments', function (Blueprint $table) {
            $table->string('size_label', 20)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('material', 100)->nullable();
            $table->decimal('measurement_chest_cm', 5, 1)->nullable();
            $table->decimal('measurement_length_cm', 5, 1)->nullable();
            $table->decimal('measurement_waist_cm', 5, 1)->nullable();
            $table->decimal('measurement_inseam_cm', 5, 1)->nullable();
            $table->decimal('measurement_shoulder_cm', 5, 1)->nullable();
            $table->decimal('measurement_sleeve_cm', 5, 1)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('garments', function (Blueprint $table) {
            $table->dropColumn([
                'size_label', 'brand', 'material',
                'measurement_chest_cm', 'measurement_length_cm', 'measurement_waist_cm',
                'measurement_inseam_cm', 'measurement_shoulder_cm', 'measurement_sleeve_cm',
            ]);
        });
    }
};
