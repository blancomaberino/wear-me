<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_link_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['thumbs_up', 'thumbs_down', 'heart', 'fire']);
            $table->string('visitor_hash', 64);
            $table->timestamp('created_at')->nullable();
            $table->unique(['share_link_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
