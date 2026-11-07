<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sale_items', 'category_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            });
        }

        if (Schema::hasTable('custom_items') && !Schema::hasColumn('custom_items', 'category_id')) {
            Schema::table('custom_items', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            });
        }

        // Seed common standard categories if they don't already exist
        $standardCategories = [
            'Repair & Service',
            'Laptop',
            'Mobile Phone',
            'Accessories',
            'Other',
        ];

        foreach ($standardCategories as $catName) {
            Category::firstOrCreate(['name' => $catName]);
        }

        // Backfill existing sale_items that have a product_id
        if (Schema::hasTable('sale_items') && Schema::hasTable('products')) {
            try {
                DB::statement("UPDATE sale_items SET category_id = (SELECT category_id FROM products WHERE products.id = sale_items.product_id) WHERE product_id IS NOT NULL AND category_id IS NULL");
            } catch (\Throwable $e) {
                // Ignore if query fails on empty or non-sqlite
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sale_items', 'category_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            });
        }

        if (Schema::hasTable('custom_items') && Schema::hasColumn('custom_items', 'category_id')) {
            Schema::table('custom_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            });
        }
    }
};
