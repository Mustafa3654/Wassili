<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->json('opening_hours')->nullable()->after('is_open');
        });

        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        $default = collect($days)->mapWithKeys(fn ($d) => [
            $d => ['is_open' => true, 'open' => '09:00', 'close' => '22:00'],
        ])->toArray();

        DB::table('vendors')->whereNull('opening_hours')->update(['opening_hours' => json_encode($default)]);
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('opening_hours');
        });
    }
};
