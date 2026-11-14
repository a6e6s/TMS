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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('reports_to_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('role', 32)->default('support_agent')->index();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['reports_to_id']);
            $table->dropSoftDeletes();
            $table->dropIndex(['role']);
            $table->dropColumn(['reports_to_id', 'role']);
        });
    }
};
