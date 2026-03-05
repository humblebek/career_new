<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Stores per-option career weights for multiple_choice,
            // or a single career weight map for scale/short_answer questions.
            // Structure: { "options": [{"Software Engineer":3,"Teacher":1,...},...] }
            //         or { "keywords": {"programming":{"Software Engineer":3},...} }
            $table->json('career_weights')->nullable()->after('options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('career_weights');
        });
    }
};
