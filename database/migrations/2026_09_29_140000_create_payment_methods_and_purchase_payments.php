<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('payment_methods')->insert([
            ['name' => 'Cash', 'code' => 'cash', 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Card', 'code' => 'card', 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bank Transfer', 'code' => 'bank_transfer', 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Other', 'code' => 'other', 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('product_purchases', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->after('supplier_id')->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_reference')->nullable()->after('payment_method_id');
            $table->string('payment_status', 20)->default('unpaid')->after('payment_reference');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('product_purchases', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn(['payment_method_id', 'payment_reference', 'payment_status', 'paid_amount']);
        });
        Schema::dropIfExists('payment_methods');
    }
};
