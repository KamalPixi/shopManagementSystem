<?php

namespace Tests\Feature;

use App\Livewire\Pos;
use App\Livewire\Sales\SaleEdit;
use App\Livewire\Sales\Sales;
use App\Models\CashRegisterSession;
use App\Models\Category;
use App\Models\CustomItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategorySalesFilterReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $repairCategory;
    private Category $laptopCategory;
    private Category $mobileCategory;
    private Category $accessoriesCategory;
    private Category $otherCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Manager Admin',
            'username' => 'manager_admin',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        foreach (['sales:read', 'sales:create', 'sales:update', 'sales:delete', 'pos:read', 'reports:read'] as $permission) {
            $this->user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));
        }

        $this->actingAs($this->user);

        Shop::create([
            'name' => 'Tech Parisien Shop',
            'address' => '10 Rue de Paris',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country' => 'France',
            'gst_percent' => 0,
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

        $this->repairCategory = Category::firstOrCreate(['name' => 'Repair & Service']);
        $this->laptopCategory = Category::firstOrCreate(['name' => 'Laptop']);
        $this->mobileCategory = Category::firstOrCreate(['name' => 'Mobile Phone']);
        $this->accessoriesCategory = Category::firstOrCreate(['name' => 'Accessories']);
        $this->otherCategory = Category::firstOrCreate(['name' => 'Other']);
    }

    public function test_pos_custom_sale_allows_category_selection_and_persists_category_id()
    {
        Livewire::test(Pos::class)
            ->call('openCustomModal')
            ->set('custom_item_name', 'iPhone iCloud Unlock Service')
            ->set('custom_category_id', $this->repairCategory->id)
            ->set('custom_price', 60.00)
            ->set('custom_purchase_price', 20.00)
            ->set('custom_quantity', 1)
            ->call('addCustomItem')
            ->set('payment_method', 'cash')
            ->call('sale');

        $sale = Sale::latest()->first();
        $this->assertNotNull($sale);

        $saleItem = $sale->saleItems()->first();
        $this->assertNotNull($saleItem);
        $this->assertEquals('iPhone iCloud Unlock Service', $saleItem->name);
        $this->assertEquals($this->repairCategory->id, $saleItem->category_id);
        $this->assertEquals('Repair & Service', $saleItem->category_name);
        $this->assertEquals(60.00, (float) $saleItem->price);
        $this->assertEquals(20.00, (float) $saleItem->purchase_price);
        $this->assertEquals(40.00, (float) $saleItem->profit);

        // Verify CustomItem directory saved category for future suggestions
        $customItem = CustomItem::where('name', 'iPhone iCloud Unlock Service')->first();
        $this->assertNotNull($customItem);
        $this->assertEquals($this->repairCategory->id, $customItem->category_id);
    }

    public function test_sale_edit_allows_viewing_and_updating_category_for_custom_items()
    {
        $sale = Sale::create([
            'receipt_no' => 'TP-900001',
            'total_price' => 50.00,
            'gst' => 0,
            'discount' => 0,
            'profit' => 35.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
        ]);

        $item = $sale->saleItems()->create([
            'item_name' => 'Custom Screen Repair',
            'category_id' => $this->repairCategory->id,
            'quantity' => 1,
            'purchase_price' => 15.00,
            'price' => 50.00,
            'discount' => 0,
            'total' => 50.00,
            'profit' => 35.00,
            'is_vatable' => false,
        ]);

        $itemKey = 'custom_' . $item->id;

        Livewire::test(SaleEdit::class, ['sale_id' => $sale->id])
            ->assertSee('Repair & Service')
            ->set("sale_items.{$itemKey}.category_id", $this->otherCategory->id)
            ->call('updateSale');

        $updatedItem = SaleItem::where('sale_id', $sale->id)->first();
        $this->assertNotNull($updatedItem);
        $this->assertEquals($this->otherCategory->id, $updatedItem->category_id);
    }

    public function test_sales_index_filters_by_category()
    {
        // Sale 1: Repair service
        $sale1 = Sale::create([
            'receipt_no' => 'TP-REP-01',
            'total_price' => 80.00,
            'gst' => 0,
            'discount' => 0,
            'profit' => 50.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'created_at' => now(),
        ]);
        $sale1->saleItems()->create([
            'item_name' => 'Battery Replacement',
            'category_id' => $this->repairCategory->id,
            'quantity' => 1,
            'purchase_price' => 30.00,
            'price' => 80.00,
            'discount' => 0,
            'total' => 80.00,
            'profit' => 50.00,
            'is_vatable' => false,
        ]);

        // Sale 2: Laptop catalog item
        $laptopProduct = Product::create([
            'name' => 'Dell Latitude 7490',
            'category_id' => $this->laptopCategory->id,
            'purchase_price' => 200.00,
            'price' => 350.00,
            'quantity' => 5,
            'is_active' => true,
        ]);

        $sale2 = Sale::create([
            'receipt_no' => 'TP-LAP-02',
            'total_price' => 350.00,
            'gst' => 0,
            'discount' => 0,
            'profit' => 150.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'created_at' => now(),
        ]);
        $sale2->saleItems()->create([
            'product_id' => $laptopProduct->id,
            'category_id' => $this->laptopCategory->id,
            'item_name' => 'Dell Latitude 7490',
            'quantity' => 1,
            'purchase_price' => 200.00,
            'price' => 350.00,
            'discount' => 0,
            'total' => 350.00,
            'profit' => 150.00,
            'is_vatable' => false,
        ]);

        // When filtering by Repair category
        Livewire::test(Sales::class)
            ->set('category_filter', (string) $this->repairCategory->id)
            ->assertSee('TP-REP-01')
            ->assertDontSee('TP-LAP-02');

        // When filtering by Laptop category
        Livewire::test(Sales::class)
            ->set('category_filter', (string) $this->laptopCategory->id)
            ->assertSee('TP-LAP-02')
            ->assertDontSee('TP-REP-01');
    }

    public function test_category_financial_breakdown_metrics_calculation()
    {
        // 1. Repair Sale: 2 services sold, Total sales = 100, Cost = 30, Profit = 70
        $sale1 = Sale::create([
            'receipt_no' => 'TP-001',
            'total_price' => 100.00,
            'gst' => 0,
            'discount' => 0,
            'profit' => 70.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'created_at' => now(),
        ]);
        $sale1->saleItems()->create([
            'item_name' => 'iPhone Screen Repair',
            'category_id' => $this->repairCategory->id,
            'quantity' => 2,
            'purchase_price' => 15.00,
            'price' => 50.00,
            'discount' => 0,
            'total' => 100.00,
            'profit' => 70.00,
            'is_vatable' => false,
        ]);

        // 2. Laptop Sale: 1 laptop sold, Total sales = 400, Cost = 250, Profit = 150
        $sale2 = Sale::create([
            'receipt_no' => 'TP-002',
            'total_price' => 400.00,
            'gst' => 0,
            'discount' => 0,
            'profit' => 150.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'created_at' => now(),
        ]);
        $sale2->saleItems()->create([
            'item_name' => 'ThinkPad T480',
            'category_id' => $this->laptopCategory->id,
            'quantity' => 1,
            'purchase_price' => 250.00,
            'price' => 400.00,
            'discount' => 0,
            'total' => 400.00,
            'profit' => 150.00,
            'is_vatable' => false,
        ]);

        // 3. Mobile Phone Sale: 1 mobile sold, Total sales = 200, Cost = 120, Profit = 80
        $sale3 = Sale::create([
            'receipt_no' => 'TP-003',
            'total_price' => 200.00,
            'gst' => 0,
            'discount' => 0,
            'profit' => 80.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'created_at' => now(),
        ]);
        $sale3->saleItems()->create([
            'item_name' => 'Samsung Galaxy A54',
            'category_id' => $this->mobileCategory->id,
            'quantity' => 1,
            'purchase_price' => 120.00,
            'price' => 200.00,
            'discount' => 0,
            'total' => 200.00,
            'profit' => 80.00,
            'is_vatable' => false,
        ]);

        $component = Livewire::test(Sales::class);

        $breakdown = $component->instance()->getCategoryBreakdown();

        $rows = $breakdown['rows'];
        $totals = $breakdown['totals'];

        // Verify Repair & Service stats
        $repairStats = $rows[$this->repairCategory->id];
        $this->assertEquals(2, $repairStats['items_count']);
        $this->assertEquals(100.00, (float) $repairStats['total_sales']);
        $this->assertEquals(30.00, (float) $repairStats['total_cost']);
        $this->assertEquals(70.00, (float) $repairStats['net_profit']);
        $this->assertEquals(70.0, (float) $repairStats['margin']);

        // Verify Laptop stats
        $laptopStats = $rows[$this->laptopCategory->id];
        $this->assertEquals(1, $laptopStats['items_count']);
        $this->assertEquals(400.00, (float) $laptopStats['total_sales']);
        $this->assertEquals(250.00, (float) $laptopStats['total_cost']);
        $this->assertEquals(150.00, (float) $laptopStats['net_profit']);
        $this->assertEquals(37.5, (float) $laptopStats['margin']);

        // Verify Mobile Phone stats
        $mobileStats = $rows[$this->mobileCategory->id];
        $this->assertEquals(1, $mobileStats['items_count']);
        $this->assertEquals(200.00, (float) $mobileStats['total_sales']);
        $this->assertEquals(120.00, (float) $mobileStats['total_cost']);
        $this->assertEquals(80.00, (float) $mobileStats['net_profit']);
        $this->assertEquals(40.0, (float) $mobileStats['margin']);

        // Verify Overall Totals
        $this->assertEquals(4, $totals['items_count']);
        $this->assertEquals(700.00, (float) $totals['total_sales']);
        $this->assertEquals(400.00, (float) $totals['total_cost']);
        $this->assertEquals(300.00, (float) $totals['net_profit']);
        $this->assertEquals(42.9, (float) $totals['margin']);

        // When selecting Repair & Service filter
        $component->set('category_filter', (string) $this->repairCategory->id);
        $filteredBreakdown = $component->instance()->getCategoryBreakdown();
        $this->assertNotNull($filteredBreakdown['selected']);
        $this->assertEquals('Repair & Service', $filteredBreakdown['selected']['name']);
        $this->assertEquals(100.00, (float) $filteredBreakdown['selected']['total_sales']);
    }

    public function test_sales_report_pdf_generation_dispatches_print_event()
    {
        $sale = Sale::create([
            'receipt_no' => 'TP-PDF-01',
            'total_price' => 120.00,
            'gst' => 0,
            'discount' => 0,
            'profit' => 60.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'created_at' => now(),
        ]);
        $sale->saleItems()->create([
            'item_name' => 'Service & Maintenance',
            'category_id' => $this->repairCategory->id,
            'quantity' => 1,
            'purchase_price' => 60.00,
            'price' => 120.00,
            'discount' => 0,
            'total' => 120.00,
            'profit' => 60.00,
            'is_vatable' => false,
        ]);

        Livewire::test(Sales::class)
            ->set('category_filter', (string) $this->repairCategory->id)
            ->call('printSalesReport')
            ->assertDispatched('print-pdf');
    }

    public function test_category_breakdown_section_is_collapsed_by_default_and_can_be_expanded()
    {
        Livewire::test(Sales::class)
            ->assertSet('show_category_breakdown', false)
            ->assertSee('Expand')
            ->assertDontSee('Total / Overall')
            ->set('show_category_breakdown', true)
            ->assertSee('Collapse')
            ->assertSee('Total / Overall');
    }
}
