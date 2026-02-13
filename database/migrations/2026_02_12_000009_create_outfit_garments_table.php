<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outfit_garments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outfit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('garment_id')->constrained()->cascadeOnDelete();
            $table->string('slot_label')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['outfit_id', 'garment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outfit_garments');
    }
};
