<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->string('payment_method', 50);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'payment_method']);
        });

        // Backfill existing sales into sale_payments so all previous sales are compatible
        $sales = DB::table('sales')->get();
        $now = now();
        $records = [];
        foreach ($sales as $sale) {
            $records[] = [
                'sale_id' => $sale->id,
                'payment_method' => $sale->payment_method ?: 'cash',
                'amount' => (float) $sale->total_price,
                'reference' => $sale->payment_reference,
                'created_at' => $sale->created_at ?: $now,
                'updated_at' => $sale->updated_at ?: $now,
            ];
        }
        if (!empty($records)) {
            DB::table('sale_payments')->insert($records);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
