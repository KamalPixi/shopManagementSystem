<?php

namespace Tests\Feature;

use App\Livewire\Pos;
use App\Livewire\Sales\Sales;
use App\Livewire\Settings;
use App\Models\CashRegisterSession;
use App\Models\Category;
use App\Models\CustomItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesReportVatProfitSettingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Report Admin',
            'username' => 'report_admin',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        foreach (['sales:read', 'sales:create', 'sales:update', 'sales:delete', 'pos:read', 'pos:create', 'reports:read', 'settings:read', 'settings:update'] as $permission) {
            $this->user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));
        }

        $this->actingAs($this->user);

        $this->category = Category::create([
            'name' => 'Mobile Phone',
        ]);

        $this->shop = Shop::create([
            'name' => 'Tech Parisien',
            'address' => '10 Rue de Paris',
            'city' => 'Paris',
            'country' => 'France',
            'gst_percent' => 20,
            'currency' => ['code' => 'EUR', 'symbol' => '€'],
            'settings' => [
                'language' => 'en',
                'deduct_vat_from_profit' => false,
            ],
        ]);

        PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Cash', 'is_active' => true]);

        CashRegisterSession::create([
            'user_id' => $this->user->id,
            'opened_at' => now(),
            'opening_balance' => 100,
            'status' => 'open',
        ]);
    }

    public function test_settings_page_toggles_and_persists_vat_deduction_flag(): void
    {
        $component = Livewire::test(Settings::class)
            ->assertSet('deduct_vat_from_profit', false)
            ->set('deduct_vat_from_profit', true)
            ->call('update')
            ->assertHasNoErrors();

        $this->shop->refresh();
        $this->assertTrue((bool) ($this->shop->settings['deduct_vat_from_profit'] ?? false));
        $this->assertTrue($this->shop->deductVatFromProfit());
    }

    public function test_when_vat_deduction_is_disabled_profit_excludes_vat_in_pos_and_reports(): void
    {
        // 1. Sale with price 10.00, cost 6.00, VAT 20% (is_vatable = true)
        $sale = Sale::create([
            'receipt_no' => 'TP-000009',
            'total_price' => 10.00,
            'gst' => 2.00,
            'discount' => 0.00,
            'profit' => 4.00,
            'created_by' => $this->user->id,
            'payment_method' => 'cash',
        ]);

        $item = SaleItem::create([
            'sale_id' => $sale->id,
            'category_id' => $this->category->id,
            'item_name' => 'Mobile',
            'quantity' => 1,
            'purchase_price' => 6.00,
            'price' => 10.00,
            'discount' => 0.00,
            'total' => 10.00,
            'profit' => 4.00,
            'is_vatable' => true,
        ]);

        // Calculated profit without VAT: (10 - 6) = 4.00
        $this->assertEquals(4.00, $item->getCalculatedProfit(false, 20.0));
        $this->assertEquals(4.00, $sale->getCalculatedProfit(false, 20.0));

        // 2. Category breakdown in Sales component
        $salesComponent = Livewire::test(Sales::class);
        $breakdown = $salesComponent->instance()->getCategoryBreakdown();

        $mobileStats = $breakdown['rows'][$this->category->id];
        $this->assertEquals(10.00, (float) $mobileStats['total_sales']);
        $this->assertEquals(6.00, (float) $mobileStats['total_cost']);
        $this->assertEquals(4.00, (float) $mobileStats['net_profit']); // NOT 2.00!
        $this->assertEquals(40.0, (float) $mobileStats['margin']);     // (4 / 10) * 100 = 40%

        // 3. Sales Report PDF generation
        $service = new SaleService();
        $pdfData = [
            'sales' => collect([$sale->load('saleItems')]),
            'shop' => $this->shop,
            'deduct_vat_from_profit' => false,
            'gst_percent' => 20.0,
            'report_date' => 'Today',
            'report_date_filter_type' => 'DAY',
            'report_print_date' => now()->toDateTimeString(),
            'selected_staff' => null,
            'receipt_filter' => null,
            'category_filter_name' => null,
            'category_breakdown' => $breakdown,
        ];
        $pdfUrl = $service->generateSalesReport($pdfData);
        $this->assertNotEmpty($pdfUrl);
    }

    public function test_when_vat_deduction_is_enabled_profit_deducts_vat_in_reports(): void
    {
        // Enable VAT deduction in shop settings
        $settings = $this->shop->settings;
        $settings['deduct_vat_from_profit'] = true;
        $this->shop->update(['settings' => $settings]);

        $sale = Sale::create([
            'receipt_no' => 'TP-000010',
            'total_price' => 10.00,
            'gst' => 2.00,
            'discount' => 0.00,
            'profit' => 2.00,
            'created_by' => $this->user->id,
            'payment_method' => 'cash',
        ]);

        $item = SaleItem::create([
            'sale_id' => $sale->id,
            'category_id' => $this->category->id,
            'item_name' => 'Mobile',
            'quantity' => 1,
            'purchase_price' => 6.00,
            'price' => 10.00,
            'discount' => 0.00,
            'total' => 10.00,
            'profit' => 2.00,
            'is_vatable' => true,
        ]);

        // Calculated profit with VAT deducted: (10 - 6) - 2.00 = 2.00
        $this->assertEquals(2.00, $item->getCalculatedProfit(true, 20.0));
        $this->assertEquals(2.00, $sale->getCalculatedProfit(true, 20.0));

        // Category breakdown in Sales component
        $salesComponent = Livewire::test(Sales::class);
        $breakdown = $salesComponent->instance()->getCategoryBreakdown();

        $mobileStats = $breakdown['rows'][$this->category->id];
        $this->assertEquals(10.00, (float) $mobileStats['total_sales']);
        $this->assertEquals(6.00, (float) $mobileStats['total_cost']);
        $this->assertEquals(2.00, (float) $mobileStats['net_profit']); // 2.00 when deduct is enabled
        $this->assertEquals(20.0, (float) $mobileStats['margin']);     // (2 / 10) * 100 = 20%
    }

    public function test_updating_settings_synchronizes_existing_sales_profit_in_database(): void
    {
        // Create a sale that was previously recorded with VAT deducted (profit = 2.00)
        $sale = Sale::create([
            'receipt_no' => 'TP-000011',
            'total_price' => 10.00,
            'gst' => 2.00,
            'discount' => 0.00,
            'profit' => 2.00,
            'created_by' => $this->user->id,
            'payment_method' => 'cash',
        ]);

        $item = SaleItem::create([
            'sale_id' => $sale->id,
            'category_id' => $this->category->id,
            'item_name' => 'Mobile',
            'quantity' => 1,
            'purchase_price' => 6.00,
            'price' => 10.00,
            'discount' => 0.00,
            'total' => 10.00,
            'profit' => 2.00,
            'is_vatable' => true,
        ]);

        // Initially in database: profit is 2.00
        $this->assertEquals(2.00, (float) $sale->profit);
        $this->assertEquals(2.00, (float) $item->profit);

        // Admin saves settings with deduct_vat_from_profit = false
        Livewire::test(Settings::class)
            ->set('deduct_vat_from_profit', false)
            ->call('update');

        // Existing sale and item profit in database are synchronized to gross profit (4.00)
        $sale->refresh();
        $item->refresh();
        $this->assertEquals(4.00, (float) $sale->profit);
        $this->assertEquals(4.00, (float) $item->profit);

        // Admin toggles setting back to true
        Livewire::test(Settings::class)
            ->set('deduct_vat_from_profit', true)
            ->call('update');

        // Database is synchronized back to 2.00
        $sale->refresh();
        $item->refresh();
        $this->assertEquals(2.00, (float) $sale->profit);
        $this->assertEquals(2.00, (float) $item->profit);
    }
}
