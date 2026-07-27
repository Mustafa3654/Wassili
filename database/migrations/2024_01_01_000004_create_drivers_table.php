<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Full international WhatsApp number, digits only (e.g. 9665XXXXXXXX).
            $table->string('phone');
            $table->enum('vehicle_type', ['motorcycle', 'car', 'bicycle'])->default('motorcycle');
            $table->enum('status', ['available', 'busy', 'offline'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
