<?php

namespace Tests\Feature;

use App\Livewire\Sales\SaleEdit;
use App\Livewire\Sales\SaleShow;
use App\Models\CashRegisterSession;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomSaleCostPriceEditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $stockProduct;
    private Sale $sale;
    private SaleItem $stockItem;
    private SaleItem $customItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Manager User',
            'username' => 'manager1',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        foreach (['sales:read', 'sales:create', 'sales:update', 'sales:delete', 'pos:read'] as $permission) {
            $this->user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));
        }

        $this->actingAs($this->user);

        Shop::create([
            'name' => 'Paris Tech Boutique',
            'address' => '10 Rue de la Paix',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country' => 'France',
            'gst_percent' => 0, // 0 for simpler math in this test
            'currency' => ['code' => 'EUR', 'symbol' => '€'],
            'settings' => ['language' => 'en'],
        ]);

        PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Cash', 'is_active' => true]);

        CashRegisterSession::create([
            'user_id' => $this->user->id,
            'opened_at' => now(),
            'opening_balance' => 100,
            'status' => 'open',
        ]);

        // Stock Product: Pendrive
        $this->stockProduct = Product::create([
            'name' => 'Pendrive 64GB',
            'sku' => 'PEN-64',
            'purchase_price' => 8.00,
            'price' => 15.00,
            'quantity' => 10,
            'is_active' => true,
        ]);

        // Create Sale with 1 Stock Product and 1 Custom Service (iPhone iCloud Unlock)
        // Pendrive: sell 15, cost 8, profit 7
        // iCloud Unlock: sell 50, initial estimated cost 10, profit 40
        // Total Sale: price 65, total profit 47
        $this->sale = Sale::create([
            'receipt_no' => 'TP-888001',
            'total_price' => 65.00,
            'discount' => 0.00,
            'gst' => 0.00,
            'profit' => 47.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
        ]);

        $this->stockItem = SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $this->stockProduct->id,
            'item_name' => $this->stockProduct->name,
            'quantity' => 1,
            'purchase_price' => 8.00,
            'price' => 15.00,
            'discount' => 0.00,
            'total' => 15.00,
            'profit' => 7.00,
            'is_vatable' => false,
        ]);

        $this->customItem = SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => null, // custom item
            'item_name' => 'iPhone iCloud Unlock',
            'quantity' => 1,
            'purchase_price' => 10.00, // initial estimated cost
            'price' => 50.00,
            'discount' => 0.00,
            'total' => 50.00,
            'profit' => 40.00,
            'is_vatable' => false,
        ]);
    }

    public function test_sale_edit_view_shows_editable_cost_price_input_for_custom_items(): void
    {
        Livewire::test(SaleEdit::class, ['sale_id' => $this->sale->id])
            ->assertSeeHtml("sale_items.custom_{$this->customItem->id}.purchase_price")
            ->assertSeeHtml(__('Editable Cost'))
            ->assertSee('iPhone iCloud Unlock')
            ->assertSee('Pendrive 64GB');
    }

    public function test_custom_item_cost_price_can_be_edited_and_recalculates_profit(): void
    {
        $customKey = "custom_{$this->customItem->id}";

        Livewire::test(SaleEdit::class, ['sale_id' => $this->sale->id])
            // Initial estimated profit is 47.00
            ->assertSet('estimated_profit', '47.00')
            // Update actual cost price from 10.00 to 25.00
            ->set("sale_items.{$customKey}.purchase_price", 25.00)
            // Real-time estimated profit becomes 32.00 (47 - 15 = 32)
            ->assertSet('estimated_profit', '32.00')
            // Save sale changes
            ->call('updateSale')
            ->assertHasNoErrors();

        // Verify Database
        $this->sale->refresh();

        // 1. Sale Total Profit updated from 47.00 to 32.00
        $this->assertEquals(32.00, (float) $this->sale->profit);

        // 2. Custom Item updated with new purchase_price = 25.00 and profit = 25.00
        $updatedCustomItem = SaleItem::where('sale_id', $this->sale->id)
            ->whereNull('product_id')
            ->first();

        $this->assertNotNull($updatedCustomItem);
        $this->assertEquals(25.00, (float) $updatedCustomItem->purchase_price);
        $this->assertEquals(25.00, (float) $updatedCustomItem->profit);

        // 3. Stock Product remains intact with purchase_price = 8.00 and profit = 7.00
        $updatedStockItem = SaleItem::where('sale_id', $this->sale->id)
            ->where('product_id', $this->stockProduct->id)
            ->first();

        $this->assertNotNull($updatedStockItem);
        $this->assertEquals(8.00, (float) $updatedStockItem->purchase_price);
        $this->assertEquals(7.00, (float) $updatedStockItem->profit);
    }

    public function test_sale_show_displays_the_updated_cost_price(): void
    {
        // Change the cost price directly as if edited
        $this->customItem->update([
            'purchase_price' => 25.00,
            'profit' => 25.00,
        ]);

        Livewire::test(SaleShow::class, ['sale_id' => $this->sale->id])
            ->assertSee('25.00');
    }
}
