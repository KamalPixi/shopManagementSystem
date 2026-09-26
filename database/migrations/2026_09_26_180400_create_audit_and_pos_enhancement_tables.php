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
        // 1. Cash Register Sessions (Opening/Closing Balance)
        Schema::create('cash_register_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->timestamp('opened_at')->useCurrent();
            $table->decimal('closing_balance', 10, 2)->nullable();
            $table->decimal('expected_closing_balance', 10, 2)->nullable();
            $table->decimal('difference', 10, 2)->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('closed_at')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 20)->default('open'); // open, closed
            $table->timestamps();
        });

        // 2. Money Categories (for cash in / out sources)
        Schema::create('money_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20)->default('both'); // in, out, both
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 3. Money Movements (Cash In / Cash Out)
        Schema::create('money_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_session_id')->nullable()->constrained('cash_register_sessions')->onDelete('set null');
            $table->string('type', 10); // in, out
            $table->decimal('amount', 10, 2);
            $table->foreignId('money_category_id')->nullable()->constrained('money_categories')->onDelete('set null');
            $table->text('reason'); // reason / remark
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // who did it
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        // 4. Product Variations
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('variation_name');
            $table->string('sku', 100)->nullable()->unique();
            $table->string('serial_no', 100)->nullable()->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->integer('low_stock_threshold')->nullable();
            $table->json('attributes_and_values')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 5. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // created, updated, deleted, cash_in, cash_out, etc.
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        // 6. Alter products table
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_vatable')->default(true)->after('price');
            $table->integer('low_stock_threshold')->nullable()->after('quantity');
            $table->boolean('has_variations')->default(false)->after('status');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
        });

        // 7. Alter sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_method', 50)->default('cash')->after('profit');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('payment_attachment')->nullable()->after('payment_reference');
            $table->foreignId('cash_register_session_id')->nullable()->after('payment_attachment')->constrained('cash_register_sessions')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
        });

        // 8. Alter sale_items table
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_variation_id')->nullable()->after('product_id')->constrained('product_variations')->onDelete('set null');
            $table->boolean('is_vatable')->default(true)->after('total');
            $table->boolean('show_warranty_on_receipt')->default(true)->after('is_vatable');
            $table->boolean('show_serial_on_receipt')->default(true)->after('show_warranty_on_receipt');
        });

        // 9. Alter shops table
        Schema::table('shops', function (Blueprint $table) {
            $table->integer('low_stock_threshold')->default(10)->after('gst_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('low_stock_threshold');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_variation_id']);
            $table->dropColumn([
                'product_variation_id',
                'is_vatable',
                'show_warranty_on_receipt',
                'show_serial_on_receipt'
            ]);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cash_register_session_id']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'payment_method',
                'payment_reference',
                'payment_attachment',
                'cash_register_session_id',
                'updated_by'
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'is_vatable',
                'low_stock_threshold',
                'has_variations',
                'updated_by'
            ]);
        });

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('money_movements');
        Schema::dropIfExists('money_categories');
        Schema::dropIfExists('cash_register_sessions');
    }
};
