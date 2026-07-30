<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        // Backfill any existing users with a username from their email prefix
        // (e.g. admin@reva.test -> "admin") so they can still sign in.
        DB::table('users')->whereNotNull('email')->update([
            'username' => DB::raw("SUBSTRING_INDEX(email, '@', 1)"),
        ]);

        // Email is no longer required for admin login.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
