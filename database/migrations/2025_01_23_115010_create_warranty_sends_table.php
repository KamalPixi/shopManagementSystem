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
        Schema::create('warranty_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->nullable()->constrained('sale_items')->onDelete('set null');
            $table->string('reference_no', 255)->nullable();
            $table->string('product_name', 255)->nullable();
            $table->text('problem_description')->nullable();
            $table->string('repair_shop_details', 255)->nullable();
            $table->json('customer_details')->nullable(); // ['name' => '', 'phone_no' => '', 'address' => '']
            $table->decimal('repair_cost', 10, 2)->default(0);
            $table->decimal('repair_cost_paid', 10, 2)->default(0);
            $table->decimal('repair_cost_due', 10, 2)->default(0);
            $table->timestamp('send_date')->nullable();
            $table->timestamp('receive_date')->nullable();
            $table->text('receive_remark')->nullable();
            $table->timestamp('customer_receive_date')->nullable();
            $table->text('customer_receive_remark')->nullable();
            $table->text('remark')->nullable(); // send,received,customer_received
            $table->string('status')->nullable(); // send,received,customer_received
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_sends');
    }
};
