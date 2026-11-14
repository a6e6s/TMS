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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ticket_number', 20)->unique();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_email')->nullable()->index();
            $table->string('locale', 8)->default('en');
            $table->string('title', 255);
            $table->text('description');
            $table->text('page_url')->nullable();
            $table->json('attachments')->nullable();
            $table->string('status', 32)->default('open')->index();
            $table->string('priority', 16)->default('medium')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
