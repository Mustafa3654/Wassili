<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        // Backfill any existing users with a username from their email prefix
        // (e.g. admin@wassili.test -> "admin") so they can still sign in.
        // Done in PHP rather than SQL: SUBSTRING_INDEX is MySQL-only and the
        // test suite runs this migration on SQLite.
        DB::table('users')->whereNotNull('email')->select('id', 'email')->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')->where('id', $user->id)->update([
                        'username' => Str::before($user->email, '@'),
                    ]);
                }
            });

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
