<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds "Continue with Google" support to customer accounts.
 * Additive only: existing customers and their data are kept.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'google_id')) $table->string('google_id')->nullable()->unique()->after('password');
            if (!Schema::hasColumn('customers', 'avatar')) $table->string('avatar', 500)->nullable()->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'google_id')) {
                $table->dropUnique(['google_id']);
                $table->dropColumn('google_id');
            }
            if (Schema::hasColumn('customers', 'avatar')) $table->dropColumn('avatar');
        });
    }
};
