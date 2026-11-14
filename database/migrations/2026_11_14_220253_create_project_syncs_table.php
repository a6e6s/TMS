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
        Schema::create('project_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('pms_provider', 32);
            $table->string('external_task_id')->nullable();
            $table->text('external_task_url')->nullable();
            $table->string('sync_status', 16)->default('pending')->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->unsignedTinyInteger('sync_attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'pms_provider']);
            $table->index(['pms_provider', 'external_task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_syncs');
    }
};
