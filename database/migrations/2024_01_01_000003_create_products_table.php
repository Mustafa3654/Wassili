<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();

            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();

            // Operational mode:
            //  - vendor_id set   => Vendor-Specific item (e.g. a restaurant menu item)
            //  - vendor_id NULL  => Universal Catalog item (supermarket/pharmacy),
            //                       a driver may buy it from any nearby store.
            $table->foreignId('vendor_id')
                  ->nullable()
                  ->constrained('vendors')
                  ->nullOnDelete();

            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
