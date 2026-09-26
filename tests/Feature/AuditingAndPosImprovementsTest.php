<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariation;
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

class AuditingAndPosImprovementsTest extends TestCase
{
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
        ]);
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

        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'variation_name' => 'Black / Silicone',
            'sku' => $varSku,
            'serial_no' => $varSn,
            'purchase_price' => 12.00,
            'price' => 30.00,
            'quantity' => 8,
            'low_stock_threshold' => 2,
        ]);

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
        $sale = Sale::latest()->first();
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
}
