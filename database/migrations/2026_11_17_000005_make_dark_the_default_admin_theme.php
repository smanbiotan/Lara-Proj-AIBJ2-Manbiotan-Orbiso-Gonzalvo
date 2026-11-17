<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dark mode becomes the default look of the admin portal.
 * Admins can still switch to Light in their account menu or Settings.
 * Non-destructive: only the column default and the theme value change.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('admins', 'theme')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->string('theme', 10)->default('dark')->change();
        });

        DB::table('admins')->where('theme', 'light')->update(['theme' => 'dark']);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('admins', 'theme')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->string('theme', 10)->default('light')->change();
        });
    }
};
