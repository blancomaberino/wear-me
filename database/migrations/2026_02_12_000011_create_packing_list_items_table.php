<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('garment_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number')->nullable();
            $table->string('occasion')->nullable();
            $table->boolean('is_packed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_list_items');
    }
};
