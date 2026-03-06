<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_results', function (Blueprint $table) {
            $table->foreignId('career_id')->nullable()->after('test_attempt_id')
                  ->constrained('careers')->nullOnDelete();
            $table->json('category_scores')->nullable()->after('detailed_analysis');
        });
    }

    public function down(): void
    {
        Schema::table('career_results', function (Blueprint $table) {
            $table->dropForeign(['career_id']);
            $table->dropColumn(['career_id', 'category_scores']);
        });
    }
};

