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
        Schema::create('custom_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->foreignId('warranty_id')->nullable()->constrained('warranties')->nullOnDelete();
            $table->string('warranty_description', 1000)->nullable();
            $table->boolean('is_vatable')->default(true);
            $table->unsignedInteger('usage_count')->default(1);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        // Backfill from historical custom items in sale_items
        if (Schema::hasTable('sale_items')) {
            try {
                $historical = DB::table('sale_items')
                    ->whereNull('product_id')
                    ->whereNotNull('item_name')
                    ->where('item_name', '!=', '')
                    ->select(
                        'item_name',
                        DB::raw('MAX(price) as price'),
                        DB::raw('MAX(purchase_price) as purchase_price'),
                        DB::raw('MAX(warranty_id) as warranty_id'),
                        DB::raw('MAX(warranty_description) as warranty_description'),
                        DB::raw('COUNT(*) as usage_count')
                    )
                    ->groupBy('item_name')
                    ->get();

                foreach ($historical as $row) {
                    $cleanName = trim($row->item_name);
                    if ($cleanName !== '') {
                        DB::table('custom_items')->insertOrIgnore([
                            'name' => $cleanName,
                            'price' => $row->price ?? 0,
                            'purchase_price' => $row->purchase_price ?? 0,
                            'warranty_id' => $row->warranty_id,
                            'warranty_description' => $row->warranty_description,
                            'is_vatable' => true,
                            'usage_count' => $row->usage_count ?? 1,
                            'last_used_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if sale_items table structure differs
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_items');
    }
};
