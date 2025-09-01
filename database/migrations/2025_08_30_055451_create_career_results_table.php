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
        Schema::create('career_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_attempt_id')->constrained()->onDelete('cascade');
            $table->string('career_title');
            $table->text('career_description');
            $table->json('career_skills'); // Array of required skills
            $table->json('career_paths'); // Array of career progression paths
            $table->integer('match_percentage');
            $table->json('detailed_analysis'); // Detailed breakdown of results
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_results');
    }
};
