<?php

namespace Tests\Feature;

use App\Livewire\Stocks\StockEdit;
use App\Livewire\Stocks\Stocks;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockCostPriceEditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Stock Manager',
            'username' => 'stock_manager',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        foreach (['stock:read', 'stock:create', 'stock:update', 'stock:delete'] as $permission) {
            $this->admin->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));
        }

        $this->actingAs($this->admin);

        $this->shop = Shop::create([
            'name' => 'Tech Paris',
            'address' => '10 Rue de la Paix',
            'city' => 'Paris',
            'postal_code' => '75002',
            'country' => 'France',
            'contact_no' => '0102030405',
            'email' => 'tech@paris.fr',
            'currency' => ['code' => 'EUR', 'symbol' => '€'],
            'settings' => ['language' => 'en'],
        ]);
    }

    public function test_single_product_cost_price_can_be_edited_and_saved(): void
    {
        $product = Product::create([
            'name' => 'USB-C Cable 2m',
            'sku' => 'SKU-CABLE-001',
            'price' => 20.00,
            'purchase_price' => 5.00, // Initial wrong cost price
            'quantity' => 10,
            'is_vatable' => 1,
            'has_variations' => 0,
            'status' => 1,
            'created_by' => $this->admin->id,
        ]);

        $purchase = $product->productPurchases()->create([
            'purchase_date' => now(),
            'purchase_price' => 5.00,
            'sale_price' => 20.00,
            'quantity' => 10,
            'payment_status' => 'paid',
            'created_by' => $this->admin->id,
        ]);

        // Edit the cost price from €5.00 to corrected €8.50
        Livewire::test(StockEdit::class, ['product_id' => $product->id])
            ->assertSet('purchase_price', '5.00')
            ->set('purchase_price', 8.50)
            ->call('update')
            ->assertHasNoErrors()
            ->assertSee(__('Product updated successfully.'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'purchase_price' => 8.50,
        ]);

        $this->assertDatabaseHas('product_purchases', [
            'id' => $purchase->id,
            'purchase_price' => 8.50,
        ]);
    }

    public function test_variation_product_cost_prices_can_be_edited_and_saved(): void
    {
        $product = Product::create([
            'name' => 'Wireless Mouse Ergonomic',
            'sku' => 'SKU-MOUSE-001',
            'price' => 35.00,
            'purchase_price' => 12.00,
            'quantity' => 25,
            'is_vatable' => 1,
            'has_variations' => 1,
            'status' => 1,
            'created_by' => $this->admin->id,
        ]);

        $var1 = $product->variations()->create([
            'variation_name' => 'Black',
            'sku' => 'SKU-MOUSE-001-BLK',
            'price' => 35.00,
            'purchase_price' => 12.00, // Mistake cost price
            'quantity' => 15,
            'created_by' => $this->admin->id,
        ]);

        $var2 = $product->variations()->create([
            'variation_name' => 'White',
            'sku' => 'SKU-MOUSE-001-WHT',
            'price' => 38.00,
            'purchase_price' => 14.00, // Mistake cost price
            'quantity' => 10,
            'created_by' => $this->admin->id,
        ]);

        // Edit both variation cost prices in StockEdit
        Livewire::test(StockEdit::class, ['product_id' => $product->id])
            ->set('variations.0.purchase_price', 15.50)
            ->set('variations.1.purchase_price', 17.00)
            ->set('purchase_price', 15.50)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_variations', [
            'id' => $var1->id,
            'purchase_price' => 15.50,
        ]);

        $this->assertDatabaseHas('product_variations', [
            'id' => $var2->id,
            'purchase_price' => 17.00,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'purchase_price' => 15.50,
        ]);
    }

    public function test_stock_list_shows_cost_price(): void
    {
        Product::create([
            'name' => 'HDMI 4K Adapter',
            'sku' => 'SKU-HDMI-4K',
            'price' => 29.99,
            'purchase_price' => 11.50,
            'quantity' => 8,
            'is_vatable' => 1,
            'has_variations' => 0,
            'status' => 1,
            'created_by' => $this->admin->id,
        ]);

        Livewire::test(Stocks::class)
            ->assertSee('HDMI 4K Adapter')
            ->assertSee('29.99')
            ->assertSee('11.50');
    }

    public function test_stock_edit_view_inputs_are_not_disabled(): void
    {
        $product = Product::create([
            'name' => 'Multi-variant Headset',
            'sku' => 'SKU-HEADSET-001',
            'price' => 50.00,
            'purchase_price' => 20.00,
            'quantity' => 5,
            'is_vatable' => 1,
            'has_variations' => 1,
            'status' => 1,
            'created_by' => $this->admin->id,
        ]);

        $product->variations()->create([
            'variation_name' => 'Standard',
            'sku' => 'SKU-HEADSET-001-STD',
            'price' => 50.00,
            'purchase_price' => 20.00,
            'quantity' => 5,
            'created_by' => $this->admin->id,
        ]);

        $html = Livewire::test(StockEdit::class, ['product_id' => $product->id])
            ->html();

        // The variations.*.purchase_price input should NOT be disabled
        $this->assertStringNotContainsString('wire:model="variations.0.purchase_price" type="number" step="0.01" min="0" class="form-control form-control-sm text-end" placeholder="0.00" disabled', $html);
        $this->assertStringContainsString('wire:model="variations.0.purchase_price"', $html);
    }
}
