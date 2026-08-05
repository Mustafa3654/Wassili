<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // GPS pin captured from the customer's browser at checkout.
            // Nullable: sharing a location is always optional.
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            // Metres of uncertainty reported by the device, so the call centre
            // knows whether the pin is trustworthy or a rough cell-tower guess.
            $table->unsignedInteger('location_accuracy')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'location_accuracy']);
        });
    }
};
