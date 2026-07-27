<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('address');
            $table->text('notes')->nullable();

            // Public, unguessable tracking id used on /track/{tracking_number}.
            $table->string('tracking_number')->unique();

            // Denormalised snapshot of the cart lines at checkout time
            // (name, qty, price, vendor, custom flag). Keeps tracking + WhatsApp
            // reproducible even if a product is later edited/deleted.
            $table->json('items')->nullable();

            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);

            $table->enum('status', ['pending', 'in_progress', 'delivered', 'cancelled'])
                  ->default('pending');

            $table->foreignId('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
