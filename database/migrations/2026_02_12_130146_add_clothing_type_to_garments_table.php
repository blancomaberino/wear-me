<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('garments', function (Blueprint $table) {
            $table->string('clothing_type', 50)->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('garments', function (Blueprint $table) {
            $table->dropColumn('clothing_type');
        });
    }
};
