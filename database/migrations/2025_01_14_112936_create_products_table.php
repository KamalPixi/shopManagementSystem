<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->foreignId('warranty_id')->nullable()->constrained('warranties')->onDelete('set null');
            $table->string('model_no', 50)->nullable();
            $table->string('serial_no', 100)->nullable()->unique(); // imei/serial_no
            $table->string('sku', 100)->nullable()->unique(); // sku/barcode
            $table->decimal('price', 10, 2); // sale price
            $table->decimal('purchase_price', 10, 2)->default(0); // will help us calculate profit
            $table->unsignedInteger('quantity')->default(0); // stock count
            $table->text('remark')->nullable();
            $table->boolean('status')->default(0); // 1=Active, 0=Inactive,
            $table->json('attributes_and_values')->nullable(); // [ ['name' => 'color', 'values' => ['Green', 'Blue', 'Grey']] ]
            $table->timestamps();
        });

        // product_purchases entries
        Schema::create('product_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->timestamp('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0); // per unit
            $table->decimal('sale_price', 10, 2)->nullable(); // to update in product sale price later,
            $table->unsignedInteger('quantity')->default(0); // Variation-specific stock
            $table->json('attributes_and_values')->nullable(); // [ ['name' => 'color', 'values' => ['Green', 'Blue', 'Grey']] ]
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_price', 10, 2); // Total price of the sale
            $table->decimal('gst', 10, 2); // GST amount
            $table->decimal('discount', 10, 2); // GST amount
            $table->decimal('profit', 10, 2)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('warranty_id')->nullable()->constrained('warranties')->onDelete('set null');
            $table->integer('quantity');
            $table->decimal('purchase_price', 10, 2); // purchase_price at sale time
            $table->decimal('price', 10, 2); // Price at the time of sale
            $table->decimal('total', 10, 2); // quantity * price
            $table->decimal('profit', 10, 2);
            $table->json('attributes_and_values')->nullable(); // [ ['name' => 'Color', 'value' => 'Green'] ]
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('contact_person', 100)->nullable();

            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->text('bank_details')->nullable();
            $table->text('notes')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_purchases');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_purchase_attributes');
    }
};
