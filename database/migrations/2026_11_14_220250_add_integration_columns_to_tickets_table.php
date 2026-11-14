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
            $table->string('source', 16)->default('manual')->after('locale')->index();
            $table->foreignId('source_app_id')->nullable()->after('source')->constrained('source_apps')->nullOnDelete();
            $table->string('tracking_token_hash', 64)->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['tracking_token_hash']);
            $table->dropForeign(['source_app_id']);
            $table->dropIndex(['source']);
            $table->dropColumn(['source', 'source_app_id', 'tracking_token_hash']);
        });
    }
};
