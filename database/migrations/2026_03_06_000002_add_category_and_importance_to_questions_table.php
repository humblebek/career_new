<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('category')->nullable()->after('career_weights');   // interests, skills, personality, goals
            $table->float('importance')->default(1.0)->after('category');      // question weight multiplier
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['category', 'importance']);
        });
    }
};

