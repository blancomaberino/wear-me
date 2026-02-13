<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('garments', function (Blueprint $table) {
            $table->string('source_url', 2048)->nullable()->after('material');
            $table->string('source_provider', 50)->nullable()->after('source_url');
            $table->string('perceptual_hash', 64)->nullable()->after('source_provider');
            $table->index('perceptual_hash');
        });
    }

    public function down(): void
    {
        Schema::table('garments', function (Blueprint $table) {
            $table->dropIndex(['perceptual_hash']);
            $table->dropColumn(['source_url', 'source_provider', 'perceptual_hash']);
        });
    }
};
