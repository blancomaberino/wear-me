<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookbook_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lookbook_id')->constrained()->cascadeOnDelete();
            $table->string('itemable_type');
            $table->unsignedBigInteger('itemable_id');
            $table->text('note')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['lookbook_id', 'itemable_type', 'itemable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lookbook_items');
    }
};
