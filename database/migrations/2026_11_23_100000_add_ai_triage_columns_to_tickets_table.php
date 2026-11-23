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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('category', 32)->nullable()->index()->after('priority');
            $table->string('ai_priority', 16)->nullable()->after('category');
            $table->decimal('ai_confidence', 4, 3)->nullable()->after('ai_priority');
            $table->text('ai_summary')->nullable()->after('ai_confidence');
            $table->string('ai_model', 128)->nullable()->after('ai_summary');
            $table->timestamp('ai_classified_at')->nullable()->after('ai_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'ai_priority', 'ai_confidence', 'ai_summary', 'ai_model', 'ai_classified_at']);
        });
    }
};
