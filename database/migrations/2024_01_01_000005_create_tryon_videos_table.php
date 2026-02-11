<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tryon_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tryon_result_id')->nullable()->constrained('tryon_results')->nullOnDelete();
            $table->foreignId('model_image_id')->constrained('model_images')->cascadeOnDelete();
            $table->foreignId('garment_id')->constrained('garments')->cascadeOnDelete();
            $table->string('video_path')->nullable();
            $table->string('kling_task_id');
            $table->string('status')->default('pending');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tryon_videos');
    }
};
