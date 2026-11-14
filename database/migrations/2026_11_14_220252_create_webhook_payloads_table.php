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
        Schema::create('webhook_payloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_app_id')->nullable()->constrained('source_apps')->nullOnDelete();
            $table->string('source', 32);
            $table->string('idempotency_scope', 191);
            $table->string('event_id', 191);
            $table->string('source_ip', 45)->nullable();
            $table->json('headers')->nullable();
            $table->json('raw_payload')->nullable();
            $table->string('processing_status', 16)->default('pending')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['idempotency_scope', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_payloads');
    }
};
