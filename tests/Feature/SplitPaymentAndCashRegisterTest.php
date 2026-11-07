<?php

namespace Tests\Feature;

use App\Livewire\CashRegister;
use App\Livewire\Pos;
use App\Models\CashRegisterSession;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SplitPaymentAndCashRegisterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;
    private CashRegisterSession $session;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Cashier Admin',
            'username' => 'cashier_admin',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        foreach (['sales:read', 'sales:create', 'sales:update', 'sales:delete', 'pos:read', 'pos:create', 'cash-register:read', 'cash-register:create', 'cash-register:update'] as $permission) {
            $this->user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));
        }

        $this->actingAs($this->user);

        $this->shop = Shop::create([
            'name' => 'Tech Parisien Shop',
            'address' => '10 Rue de Paris',
            'city' => 'Paris',
            'country' => 'France',
            'gst_percent' => 0,
            'currency' => ['code' => 'EUR', 'symbol' => '€'],
            'settings' => ['language' => 'en'],
        ]);

        PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Cash', 'is_active' => true, 'sort_order' => 1]);
        PaymentMethod::firstOrCreate(['code' => 'card'], ['name' => 'Card', 'is_active' => true, 'sort_order' => 2]);
        PaymentMethod::firstOrCreate(['code' => 'bank_transfer'], ['name' => 'Bank Transfer', 'is_active' => true, 'sort_order' => 3]);
        PaymentMethod::firstOrCreate(['code' => 'other'], ['name' => 'Other', 'is_active' => true, 'sort_order' => 4]);

        $this->session = CashRegisterSession::create([
            'user_id' => $this->user->id,
            'opening_balance' => 100.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->product = Product::create([
            'name' => 'Smartphone Pro',
            'sku' => 'PHONE-PRO-100',
            'purchase_price' => 60.00,
            'price' => 100.00,
            'quantity' => 20,
            'is_vatable' => false,
        ]);
    }

    public function test_pos_single_payment_creates_sale_payment_and_updates_cash_drawer_correctly(): void
    {
        Livewire::test(Pos::class)
            ->set('identifier', $this->product->sku)
            ->call('add')
            ->set('payment_method', 'cash')
            ->call('sale')
            ->assertHasNoErrors();

        $sale = Sale::with('payments')->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('cash', $sale->payment_method);
        $this->assertCount(1, $sale->payments);
        $this->assertEquals(100.00, (float) $sale->payments->first()->amount);
        $this->assertEquals('cash', $sale->payments->first()->payment_method);

        // Expected cash in drawer: 100 opening + 100 cash sale = 200.00
        $this->assertEquals(200.00, $this->session->calculateExpectedCash());
    }

    public function test_pos_split_payment_with_cash_and_card_records_both_payments_and_isolates_cash_in_drawer(): void
    {
        $component = Livewire::test(Pos::class)
            ->set('identifier', $this->product->sku)
            ->call('add');

        // Total is 100.00
        $this->assertEquals('100.00', $component->get('grand_total'));

        // Toggle Split Payment mode
        $component->call('toggleSplitPayment')
            ->assertSet('is_split_payment', true);

        // Allocate Cash: €40.00, Card: €60.00 with reference
        $split = [
            'cash' => ['amount' => '40.00', 'reference' => 'Cash in hand'],
            'card' => ['amount' => '60.00', 'reference' => 'AUTH-CB-7890'],
            'bank_transfer' => ['amount' => '0.00', 'reference' => ''],
            'other' => ['amount' => '0.00', 'reference' => ''],
        ];

        $component->set('split_payments', $split)
            ->call('sale')
            ->assertHasNoErrors();

        $sale = Sale::with('payments')->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('split', $sale->payment_method);
        $this->assertTrue($sale->isSplitPayment());
        $this->assertCount(2, $sale->payments);

        $cashPayment = $sale->payments->firstWhere('payment_method', 'cash');
        $cardPayment = $sale->payments->firstWhere('payment_method', 'card');

        $this->assertNotNull($cashPayment);
        $this->assertEquals(40.00, (float) $cashPayment->amount);

        $this->assertNotNull($cardPayment);
        $this->assertEquals(60.00, (float) $cardPayment->amount);
        $this->assertEquals('AUTH-CB-7890', $cardPayment->reference);

        // CRITICAL: Cash drawer must ONLY increase by the CASH portion (+40.00), NOT the card portion (+60.00)!
        // Opening balance 100.00 + Cash 40.00 = 140.00
        $this->assertEquals(140.00, $this->session->calculateExpectedCash());

        // Cash register payment breakdown:
        $breakdown = $this->session->calculatePaymentBreakdown();
        $this->assertEquals(40.00, $breakdown['cash']);
        $this->assertEquals(60.00, $breakdown['card']);
        $this->assertEquals(100.00, $breakdown['total_sales']);
        $this->assertEquals(60.00, $breakdown['non_cash_total']);
    }

    public function test_pos_split_payment_rejects_unbalanced_amounts(): void
    {
        $component = Livewire::test(Pos::class)
            ->set('identifier', $this->product->sku)
            ->call('add')
            ->call('toggleSplitPayment');

        // Total is 100.00, but user only allocates €40 Cash and €30 Card (total 70.00, 30 missing)
        $unbalanced = [
            'cash' => ['amount' => '40.00', 'reference' => ''],
            'card' => ['amount' => '30.00', 'reference' => ''],
            'bank_transfer' => ['amount' => '0.00', 'reference' => ''],
            'other' => ['amount' => '0.00', 'reference' => ''],
        ];

        $component->set('split_payments', $unbalanced)
            ->call('sale');

        // Sale should NOT be created
        $this->assertDatabaseMissing('sales', [
            'total_price' => 100.00,
            'payment_method' => 'split',
        ]);
    }

    public function test_fill_remainder_helper_calculates_correct_balance(): void
    {
        $component = Livewire::test(Pos::class)
            ->set('identifier', $this->product->sku)
            ->call('add')
            ->call('toggleSplitPayment');

        // Set cash to €35.00
        $component->set('split_payments.cash.amount', '35.00');

        // Call fillRemainder for 'card'
        $component->call('fillRemainder', 'card');

        // Card amount should be automatically 65.00 (100 - 35 = 65)
        $this->assertEquals('65.00', $component->get('split_payments.card.amount'));
        $this->assertEquals(100.00, $component->instance()->getSplitPaidTotal());
        $this->assertEquals(0.00, $component->instance()->getSplitRemainingAmount());
    }

    public function test_receipts_and_invoices_render_split_payment_breakdown(): void
    {
        $sale = Sale::create([
            'receipt_no' => 'TP-999001',
            'total_price' => 100.00,
            'gst' => 0.00,
            'discount' => 0.00,
            'profit' => 40.00,
            'created_by' => $this->user->id,
            'payment_method' => 'split',
            'cash_register_session_id' => $this->session->id,
        ]);

        SalePayment::create([
            'sale_id' => $sale->id,
            'payment_method' => 'cash',
            'amount' => 30.00,
        ]);

        SalePayment::create([
            'sale_id' => $sale->id,
            'payment_method' => 'card',
            'amount' => 70.00,
            'reference' => 'VISA-4422',
        ]);

        // 1. 80mm Thermal Receipt View
        $thermalReceiptHtml = view('prints.sale-receipt', [
            'sale' => $sale->load(['saleItems', 'payments', 'customer', 'createdBy']),
            'shop' => $this->shop,
        ])->render();

        $this->assertStringContainsString('Split Payment', $thermalReceiptHtml);
        $this->assertStringContainsString('Cash', $thermalReceiptHtml);
        $this->assertStringContainsString('30.00', $thermalReceiptHtml);
        $this->assertStringContainsString('Card', $thermalReceiptHtml);
        $this->assertStringContainsString('70.00', $thermalReceiptHtml);

        // 2. A4 Invoice View
        $a4ReceiptHtml = view('prints.sale-receipt-a4', [
            'sale' => $sale->load(['saleItems', 'payments', 'customer', 'createdBy']),
            'shop' => $this->shop,
        ])->render();

        $this->assertStringContainsString('30.00', $a4ReceiptHtml);
        $this->assertStringContainsString('70.00', $a4ReceiptHtml);
    }

    public function test_cash_register_view_renders_terminal_totals_and_session_details(): void
    {
        // Create a split sale: €25 cash, €75 card
        $sale = Sale::create([
            'receipt_no' => 'TP-999002',
            'total_price' => 100.00,
            'gst' => 0.00,
            'discount' => 0.00,
            'profit' => 40.00,
            'created_by' => $this->user->id,
            'payment_method' => 'split',
            'cash_register_session_id' => $this->session->id,
        ]);

        SalePayment::create(['sale_id' => $sale->id, 'payment_method' => 'cash', 'amount' => 25.00]);
        SalePayment::create(['sale_id' => $sale->id, 'payment_method' => 'card', 'amount' => 75.00]);

        // Test Livewire component
        Livewire::test(CashRegister::class)
            ->assertSee('Shift Revenue &amp; Payment Methods Breakdown', false)
            ->assertSee('Card Terminal Total')
            ->assertSee('75.00')
            ->assertSee('25.00');

        // Test Session Details route
        $response = $this->get(route('cashRegister.details', $this->session->id));
        $response->assertOk();
        $response->assertSee('75.00');
        $response->assertSee('25.00');
    }
}
