<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('automation_logs')) {
            return;
        }

        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->string('workflow', 40)->index();       // assistant | appointment_email | reminder
            $table->string('event', 60)->index();           // appointment.created, chat.request, reminder.email ...
            $table->string('idempotency_key')->nullable()->unique();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('appointment_reference', 20)->nullable()->index();
            $table->string('status', 30)->index();          // pending, sent_to_n8n, skipped, failed, email_sent, email_failed, success
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('payload')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
    }
};
