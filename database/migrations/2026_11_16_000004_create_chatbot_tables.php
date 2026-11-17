<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('chatbot_conversations')) {
            Schema::create('chatbot_conversations', function (Blueprint $table) {
                $table->id();
                $table->string('session_token', 64)->unique();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status', 20)->default('open');
                $table->timestamp('escalated_at')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chatbot_messages')) {
            Schema::create('chatbot_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chatbot_conversation_id')->constrained()->cascadeOnDelete();
                $table->string('role', 12);
                $table->text('content');
                $table->string('source', 20)->default('n8n');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
        Schema::dropIfExists('chatbot_conversations');
    }
};
