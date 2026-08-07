<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Weekly shift, same shape as vendors.opening_hours:
            // { "monday": { "is_open": true, "open": "09:00", "close": "22:00" }, ... }
            // Null means "no shift configured" and the driver is treated as
            // always on duty, so nobody disappears before hours are filled in.
            $table->json('working_hours')->nullable()->after('vehicle_type');

            // Optional per-driver delivery fee in USD. Null keeps the order on
            // the base + multi-vendor fee from Settings.
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('working_hours');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['working_hours', 'delivery_fee']);
        });
    }
};
