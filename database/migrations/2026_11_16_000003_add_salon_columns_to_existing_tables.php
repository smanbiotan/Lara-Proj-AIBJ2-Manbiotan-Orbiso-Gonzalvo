<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive upgrade of the original tables. No column is dropped and no record is removed.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'password')) $table->string('password')->nullable()->after('phone');
            if (!Schema::hasColumn('customers', 'remember_token')) $table->rememberToken();
            if (!Schema::hasColumn('customers', 'last_login_at')) $table->timestamp('last_login_at')->nullable();
        });

        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'remember_token')) $table->rememberToken();
            if (!Schema::hasColumn('admins', 'theme')) $table->string('theme', 10)->default('light');
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'reference')) $table->string('reference', 20)->nullable()->unique()->after('id');
            if (!Schema::hasColumn('appointments', 'service_id')) $table->foreignId('service_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            if (!Schema::hasColumn('appointments', 'duration_minutes')) $table->unsignedSmallInteger('duration_minutes')->default(60)->after('appointment_time');
            if (!Schema::hasColumn('appointments', 'source')) $table->string('source', 20)->default('website');
            if (!Schema::hasColumn('appointments', 'submission_token')) $table->string('submission_token', 64)->nullable()->unique();
            if (!Schema::hasColumn('appointments', 'admin_notes')) $table->text('admin_notes')->nullable();
            if (!Schema::hasColumn('appointments', 'status_changed_at')) $table->timestamp('status_changed_at')->nullable();
            if (!Schema::hasColumn('appointments', 'reminder_status')) $table->string('reminder_status', 20)->nullable();
            if (!Schema::hasColumn('appointments', 'reminder_claimed_at')) $table->timestamp('reminder_claimed_at')->nullable();
            if (!Schema::hasColumn('appointments', 'reminder_sent_at')) $table->timestamp('reminder_sent_at')->nullable();
        });

        Schema::table('inquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('inquiries', 'source')) $table->string('source', 20)->default('contact_form');
            if (!Schema::hasColumn('inquiries', 'admin_response')) $table->text('admin_response')->nullable();
            if (!Schema::hasColumn('inquiries', 'responded_at')) $table->timestamp('responded_at')->nullable();
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: these columns may hold live salon data.
    }
};
