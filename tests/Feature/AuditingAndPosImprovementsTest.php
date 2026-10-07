<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Supplier;
use App\Models\CashRegisterSession;
use App\Models\MoneyCategory;
use App\Models\MoneyMovement;
use App\Models\AuditLog;
use App\Models\Sale;
use App\Models\Shop;
use Livewire\Livewire;
use App\Livewire\CashRegister;
use App\Livewire\MoneyMovements;
use App\Livewire\Pos;
use App\Livewire\Sales\Sales;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditingAndPosImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['username' => 'testadmin'],
            [
                'name' => 'Test Admin',
                'user_type' => 'admin',
                'password' => Hash::make('password')
            ]
        );

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        foreach ([
            'sales:read', 'sales:create', 'sales:update', 'sales:delete',
            'pos:read',
            'stock:read', 'stock:create', 'stock:update', 'stock:delete',
            'cash-register:create', 'cash-register:read', 'cash-register:update',
            'money-movements:create', 'money-movements:read', 'money-movements:delete',
            'audit-logs:read',
        ] as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $role->syncPermissions(\Spatie\Permission\Models\Permission::all());

        if (!$this->user->hasRole('admin')) {
            $this->user->assignRole($role);
        }

        $this->actingAs($this->user);

        Shop::firstOrCreate([
            'name' => 'TECH PARISIEN',
        ], [
            'address' => 'NO. 52, JALAN LANDAK',
            'city' => 'PUDU',
            'state' => 'KUALA LUMPUR',
            'postal_code' => '55100',
            'country' => 'France',
            'gst_percent' => 10,
            'low_stock_threshold' => 5,
            'currency' => ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
            'timezone' => 'Europe/Paris',
        ]);

        \App\Models\Category::firstOrCreate(['id' => 1], ['name' => 'General', 'description' => 'General Category']);
        \App\Models\Brand::firstOrCreate(['id' => 1], ['name' => 'General Brand', 'description' => 'General Brand']);
    }

    public function test_cash_register_session_opening_and_closing(): void
    {
        // Close any open session first
        CashRegisterSession::where('status', 'open')->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $this->user->id,
            'closing_balance' => 0,
        ]);

        Livewire::test(CashRegister::class)
            ->set('opening_balance', 150.00)
            ->call('startSession')
            ->assertHasNoErrors();

        $openSession = CashRegisterSession::currentOpenSession();
        $this->assertNotNull($openSession);
        $this->assertEquals(150.00, (float)$openSession->opening_balance);
        $this->assertEquals($this->user->id, $openSession->user_id);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'register_opened',
            'user_id' => $this->user->id,
        ]);

        // Close session
        Livewire::test(CashRegister::class)
            ->set('closing_balance', 200.00)
            ->set('note', 'Evening closing float counted')
            ->call('endSession')
            ->assertHasNoErrors();

        $openSession->refresh();
        $this->assertEquals('closed', $openSession->status);
        $this->assertEquals(200.00, (float)$openSession->closing_balance);
        $this->assertEquals($this->user->id, $openSession->closed_by);

        // Verify closing audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'register_closed',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_money_movements_cash_in_and_cash_out(): void
    {
        CashRegisterSession::create([
            'user_id' => $this->user->id,
            'opening_balance' => 100.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $category = MoneyCategory::firstOrCreate(
            ['name' => 'Test Capital Injection ' . uniqid(), 'type' => 'in'],
            ['description' => 'Test category']
        );

        // Test Cash In
        Livewire::test(MoneyMovements::class)
            ->set('type', 'in')
            ->set('money_category_id', $category->id)
            ->set('amount', 500.00)
            ->set('reason', 'Owner injected cash')
            ->call('recordMovement')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('money_movements', [
            'type' => 'in',
            'money_category_id' => $category->id,
            'amount' => 500.00,
            'user_id' => $this->user->id,
            'reason' => 'Owner injected cash',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'money_in',
            'user_id' => $this->user->id,
        ]);

        // Test Cash Out (reason/remark is mandatory)
        Livewire::test(MoneyMovements::class)
            ->set('type', 'out')
            ->set('amount', 75.00)
            ->set('reason', 'Purchased cleaning supplies for shop')
            ->call('recordMovement')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('money_movements', [
            'type' => 'out',
            'amount' => 75.00,
            'user_id' => $this->user->id,
            'reason' => 'Purchased cleaning supplies for shop',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'money_out',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_product_variations_and_barcode_lookup(): void
    {
        $unique = uniqid();
        $product = Product::create([
            'name' => 'iPhone 15 Case ' . $unique,
            'category_id' => 1,
            'brand_id' => 1,
            'purchase_price' => 10.00,
            'price' => 25.00,
            'quantity' => 20,
            'has_variations' => true,
            'is_vatable' => true,
            'low_stock_threshold' => 4,
            'updated_by' => $this->user->id,
        ]);

        $varSku = 'SKU-' . $unique;
        $varSn = 'SN-' . $unique;

        $supplier = Supplier::create([
            'name' => 'Supplier ' . $unique,
            'phone' => '12345678',
        ]);

        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'variation_name' => 'Black / Silicone',
            'sku' => $varSku,
            'serial_no' => $varSn,
            'purchase_price' => 12.00,
            'price' => 30.00,
            'quantity' => 8,
            'low_stock_threshold' => 2,
        ]);

        $this->assertEquals($supplier->id, $variation->supplier->id);
        $this->assertEquals($supplier->name, $variation->supplier->name);

        // Verify Product::findBySkuSerialOrModel finds the variation
        $foundBySku = Product::findBySkuSerialOrModel($varSku);
        $this->assertNotNull($foundBySku);
        $this->assertEquals($product->id, $foundBySku->id);
        $this->assertNotNull($foundBySku->matchedVariation);
        $this->assertEquals($variation->id, $foundBySku->matchedVariation->id);

        $foundBySn = Product::findBySkuSerialOrModel($varSn);
        $this->assertNotNull($foundBySn);
        $this->assertEquals($product->id, $foundBySn->id);
        $this->assertEquals($variation->id, $foundBySn->matchedVariation->id);
    }

    public function test_pos_vat_freedom_print_toggles_and_payment_methods(): void
    {
        // Ensure an open register session
        CashRegisterSession::where('status', 'open')->update(['status' => 'closed']);
        $session = CashRegisterSession::create([
            'user_id' => $this->user->id,
            'opening_balance' => 100.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $unique = uniqid();
        $batSku = 'BAT-' . $unique;
        $srvSku = 'SRV-' . $unique;

        // Create one VATable product and one non-VATable product
        $vatableProd = Product::create([
            'name' => 'Laptop Battery ' . $unique,
            'category_id' => 1,
            'brand_id' => 1,
            'sku' => $batSku,
            'serial_no' => 'SN-B-' . $unique,
            'purchase_price' => 50.00,
            'price' => 100.00,
            'quantity' => 10,
            'is_vatable' => true,
        ]);

        $nonVatableProd = Product::create([
            'name' => 'Service Repair Charge ' . $unique,
            'category_id' => 1,
            'brand_id' => 1,
            'sku' => $srvSku,
            'purchase_price' => 0.00,
            'price' => 50.00,
            'quantity' => 99,
            'is_vatable' => false,
        ]);

        $pos = Livewire::test(Pos::class)
            ->set('identifier', $batSku)
            ->call('add')
            ->set('identifier', $srvSku)
            ->call('add');

        $saleItems = $pos->get('sale_items');
        $this->assertCount(2, $saleItems);

        $keys = array_keys($saleItems);
        $batKey = $keys[0];
        $srvKey = $keys[1];

        $this->assertTrue((bool)$saleItems[$batKey]['is_vatable']);
        $this->assertFalse((bool)$saleItems[$srvKey]['is_vatable']);

        // Toggle print options and freedom to toggle VAT
        $pos->set("sale_items.{$batKey}.show_serial_on_receipt", false)
            ->set("sale_items.{$batKey}.show_warranty_on_receipt", false)
            ->set('payment_method', 'bank_transfer')
            ->set('payment_reference', 'TRX-99887766')
            ->call('sale')
            ->assertHasNoErrors();

        // Check Sale was created
        $sale = Sale::latest()->first();
        $this->assertNotNull($sale);
        $this->assertStringStartsWith('TP-', $sale->receipt_no);
        $this->assertEquals('bank_transfer', $sale->payment_method);
        $this->assertEquals('TRX-99887766', $sale->payment_reference);
        $this->assertEquals($session->id, $sale->cash_register_session_id);

        // Check SaleItems flags
        $savedItems = $sale->saleItems;
        $this->assertCount(2, $savedItems);
        $savedBat = $savedItems->firstWhere('product_id', $vatableProd->id);
        $this->assertNotNull($savedBat);
        $this->assertTrue((bool)$savedBat->is_vatable);
        $this->assertFalse((bool)$savedBat->show_serial_on_receipt);
        $this->assertFalse((bool)$savedBat->show_warranty_on_receipt);

        // Check audit log was recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'pos_sale',
            'user_id' => $this->user->id,
            'model_id' => $sale->id,
        ]);
    }

    public function test_sales_filtering_by_receipt_staff_and_payment(): void
    {
        $sale = Sale::create([
            'receipt_no' => 'TP-999999',
            'created_by' => $this->user->id,
            'payment_method' => 'bank_transfer',
            'total_price' => 50.00,
            'discount' => 0,
            'gst' => 0,
            'profit' => 10.00,
        ]);
        $this->assertNotNull($sale);

        // Test filtering by receipt number
        Livewire::test(Sales::class)
            ->set('receipt_search', $sale->receipt_no)
            ->assertViewHas('sales', function ($sales) use ($sale) {
                return $sales->contains('id', $sale->id);
            });

        // Test filtering by staff
        Livewire::test(Sales::class)
            ->set('staff_id', $this->user->id)
            ->assertViewHas('sales', function ($sales) use ($sale) {
                return $sales->contains('id', $sale->id);
            });

        // Test filtering by payment method
        Livewire::test(Sales::class)
            ->set('payment_method_filter', 'bank_transfer')
            ->assertViewHas('sales', function ($sales) use ($sale) {
                return $sales->contains('id', $sale->id);
            });
    }

    public function test_pos_blocks_selling_without_todays_opening_balance(): void
    {
        // Close all open sessions
        CashRegisterSession::where('status', 'open')->update(['status' => 'closed']);

        $unique = uniqid();
        $prod = Product::create([
            'name' => 'Test Item ' . $unique,
            'category_id' => 1,
            'brand_id' => 1,
            'sku' => 'TEST-' . $unique,
            'purchase_price' => 10.00,
            'price' => 20.00,
            'quantity' => 5,
        ]);

        // Attempting to sell without an open register session must be blocked
        $pos = Livewire::test(Pos::class)
            ->set('identifier', $prod->sku)
            ->call('add')
            ->call('sale');

        $pos->assertSet('showOpenRegisterModal', true);
        $this->assertNotEmpty($pos->get('errorMessage'));

        // User enters opening balance manually
        $pos->set('pos_opening_balance', 120.00)
            ->set('pos_opening_note', 'Staff manually entered float')
            ->call('submitOpeningBalance')
            ->assertSet('showOpenRegisterModal', false);

        $session = CashRegisterSession::currentOpenSession();
        $this->assertNotNull($session);
        $this->assertEquals(120.00, (float)$session->opening_balance);
        $this->assertEquals($this->user->id, $session->user_id);

        // Now sale can proceed
        $pos->call('sale')
            ->assertHasNoErrors();
    }

    public function test_pos_enforces_closing_previous_day_unclosed_register_before_opening_today(): void
    {
        // Setup an unclosed session from yesterday
        CashRegisterSession::where('status', 'open')->update(['status' => 'closed']);

        $yesterdaySession = CashRegisterSession::create([
            'user_id' => $this->user->id,
            'opening_balance' => 150.00,
            'status' => 'open',
            'opened_at' => now()->subDay()->setTime(9, 0, 0),
        ]);

        $unique = uniqid();
        $prod = Product::create([
            'name' => 'Item ' . $unique,
            'category_id' => 1,
            'brand_id' => 1,
            'sku' => 'SKU-' . $unique,
            'purchase_price' => 5.00,
            'price' => 15.00,
            'quantity' => 10,
        ]);

        // Attempting to sell today must detect the previous day session and show close modal
        $pos = Livewire::test(Pos::class)
            ->set('identifier', $prod->sku)
            ->call('add')
            ->call('sale');

        $pos->assertSet('showClosePreviousDayModal', true);
        $this->assertStringContainsString('Unclosed register from', $pos->get('errorMessage'));

        // Staff closes yesterday's session
        $pos->set('pos_closing_balance', 150.00)
            ->set('pos_closing_note', 'Closed on next morning by staff')
            ->call('submitClosePreviousDay')
            ->assertSet('showClosePreviousDayModal', false)
            ->assertSet('showOpenRegisterModal', true); // Immediately prompts for today's opening balance

        $yesterdaySession->refresh();
        $this->assertEquals('closed', $yesterdaySession->status);

        // Audit log reflects delayed closure
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'register_closed_delayed',
            'user_id' => $this->user->id,
        ]);

        // Staff enters today's opening balance
        $pos->set('pos_opening_balance', 100.00)
            ->set('pos_opening_note', 'Today float')
            ->call('submitOpeningBalance')
            ->assertSet('showOpenRegisterModal', false);

        $todaySession = CashRegisterSession::todayOpenSession();
        $this->assertNotNull($todaySession);
        $this->assertEquals(100.00, (float)$todaySession->opening_balance);

        // Now selling works cleanly
        $pos->call('sale')
            ->assertHasNoErrors();
    }
}
