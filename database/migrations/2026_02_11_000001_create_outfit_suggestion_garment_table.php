<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outfit_suggestion_garment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outfit_suggestion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('garment_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['outfit_suggestion_id', 'garment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outfit_suggestion_garment');
    }
};
