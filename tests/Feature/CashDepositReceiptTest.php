<?php

namespace Tests\Feature;

use App\Livewire\Deposits\Deposits;
use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\MoneyMovement;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use App\Services\DepositService;
use App\Services\PrinterManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashDepositReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Store Manager',
            'username' => 'manager_dep',
            'password' => bcrypt('secret'),
            'user_type' => 'admin',
        ]);

        foreach (['deposits:read', 'deposits:create', 'deposits:update', 'deposits:delete', 'sales:read', 'cash-register:read'] as $perm) {
            $this->user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]));
        }

        $this->actingAs($this->user);

        Shop::create([
            'name' => 'Tech Boutique Paris',
            'address' => '15 Avenue Montaigne',
            'city' => 'Paris',
            'postal_code' => '75008',
            'country' => 'France',
            'contact_no' => '0144556677',
            'email' => 'contact@techboutique.fr',
            'currency' => ['code' => 'EUR', 'symbol' => '€'],
            'settings' => ['language' => 'en'],
        ]);

        PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Cash', 'is_active' => true]);
        PaymentMethod::firstOrCreate(['code' => 'card'], ['name' => 'Card', 'is_active' => true]);
    }

    public function test_deposits_page_is_accessible_via_routes(): void
    {
        $response1 = $this->get('/deposits');
        $response1->assertOk();
        $response1->assertSee('Deposits / Advances');

        $response2 = $this->get('/deposits/index');
        $response2->assertOk();
        $response2->assertSee('Deposits / Advances');
    }

    public function test_deposits_do_not_affect_sales_cash_drawer_or_money_movements(): void
    {
        // Setup open cash register session
        $session = CashRegisterSession::create([
            'user_id' => $this->user->id,
            'opening_balance' => 200.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $initialSalesCount = Sale::count();
        $initialMovementsCount = MoneyMovement::count();
        $initialOpeningBalance = $session->fresh()->opening_balance;

        // Create a new deposit receipt for €75 advance on a €200 repair
        Livewire::test(Deposits::class)
            ->set('customer_name', 'Sophie Martin')
            ->set('customer_phone', '0612345678')
            ->set('customer_email', 'sophie@example.com')
            ->set('item_description', 'MacBook Air M1 Screen Replacement & Keyboard Clean')
            ->set('total_amount', '200.00')
            ->set('deposit_amount', '75.00')
            ->set('payment_method', 'cash')
            ->call('saveDeposit')
            ->assertHasNoErrors();

        // 1. Verify Deposit created
        $this->assertDatabaseHas('deposits', [
            'customer_name' => 'Sophie Martin',
            'total_amount' => 200.00,
            'deposit_amount' => 75.00,
            'remaining_balance' => 125.00,
            'status' => 'pending',
        ]);

        // 2. Strict Financial Isolation Check:
        // NO new sales created
        $this->assertSame($initialSalesCount, Sale::count(), 'Sales count must NOT change when a deposit is made.');
        // NO money movements created
        $this->assertSame($initialMovementsCount, MoneyMovement::count(), 'Money movements must NOT change when a deposit is made.');
        // Cash register session opening balance untouched
        $this->assertEquals($initialOpeningBalance, $session->fresh()->opening_balance, 'Cash drawer opening balance must NOT change.');
    }

    public function test_deposit_calculation_reference_generation_and_completion(): void
    {
        // Create first deposit
        Livewire::test(Deposits::class)
            ->set('customer_name', 'Jean Dupont')
            ->set('customer_phone', '0699887766')
            ->set('item_description', 'iPhone 14 Battery Replacement')
            ->set('total_amount', '90.00')
            ->set('deposit_amount', '30.00')
            ->set('payment_method', 'card')
            ->call('saveDeposit')
            ->assertHasNoErrors();

        $deposit = Deposit::where('customer_name', 'Jean Dupont')->first();
        $this->assertNotNull($deposit);
        $this->assertSame('DEP-000001', $deposit->reference_no);
        $this->assertEquals(90.00, (float) $deposit->total_amount);
        $this->assertEquals(30.00, (float) $deposit->deposit_amount);
        $this->assertEquals(60.00, (float) $deposit->remaining_balance);
        $this->assertSame('pending', $deposit->status);

        // Mark as completed when customer picks up
        Livewire::test(Deposits::class)
            ->call('markCompleted', $deposit->id)
            ->assertHasNoErrors();

        $this->assertSame('completed', $deposit->fresh()->status);
        $this->assertEquals(0.00, (float) $deposit->fresh()->remaining_balance);
    }

    public function test_deposit_pdf_service_generates_both_80mm_and_a4_receipts_in_en_and_fr(): void
    {
        $deposit = Deposit::create([
            'reference_no' => 'DEP-000042',
            'customer_name' => 'Pierre Durand',
            'customer_phone' => '0711223344',
            'customer_email' => 'pierre@example.com',
            'item_description' => 'iPad Pro 11-inch charging port repair',
            'total_amount' => 120.00,
            'deposit_amount' => 40.00,
            'remaining_balance' => 80.00,
            'payment_method' => 'cash',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $service = new DepositService();

        $url80En = $service->generateDepositReceipt($deposit->id, 'en');
        $url80Fr = $service->generateDepositReceipt($deposit->id, 'fr');
        $urlA4En = $service->generateDepositReceiptA4($deposit->id, 'en');
        $urlA4Fr = $service->generateDepositReceiptA4($deposit->id, 'fr');

        $this->assertStringContainsString('deposit-receipts/deposit_receipt_DEP-000042_en.pdf', $url80En);
        $this->assertStringContainsString('deposit-receipts/deposit_receipt_DEP-000042_fr.pdf', $url80Fr);
        $this->assertStringContainsString('deposit-receipts/deposit_receipt_a4_DEP-000042_en.pdf', $urlA4En);
        $this->assertStringContainsString('deposit-receipts/deposit_receipt_a4_DEP-000042_fr.pdf', $urlA4Fr);
    }

    public function test_deposits_livewire_component_dispatches_print_events(): void
    {
        $deposit = Deposit::create([
            'reference_no' => 'DEP-000099',
            'customer_name' => 'Lucie Bernard',
            'item_description' => 'Advance for Custom Gaming PC Assembly',
            'total_amount' => 1500.00,
            'deposit_amount' => 500.00,
            'remaining_balance' => 1000.00,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        Livewire::test(Deposits::class)
            ->call('printDepositReceipt', $deposit->id, 'en')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, 'deposit_receipt_DEP-000099_en.pdf');
            });

        Livewire::test(Deposits::class)
            ->call('printDepositReceipt', $deposit->id, 'fr')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, 'deposit_receipt_DEP-000099_fr.pdf');
            });

        Livewire::test(Deposits::class)
            ->call('printDepositReceiptA4', $deposit->id, 'en')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, 'deposit_receipt_a4_DEP-000099_en.pdf');
            });
    }

    public function test_deposits_index_search_and_filters(): void
    {
        Deposit::create([
            'reference_no' => 'DEP-000101',
            'customer_name' => 'Alice Wonder',
            'item_description' => 'Keyboard Switch Repair',
            'total_amount' => 60.00,
            'deposit_amount' => 20.00,
            'remaining_balance' => 40.00,
            'status' => 'pending',
        ]);

        Deposit::create([
            'reference_no' => 'DEP-000102',
            'customer_name' => 'Bob Builder',
            'item_description' => 'Drill Battery Re-cell',
            'total_amount' => 80.00,
            'deposit_amount' => 80.00,
            'remaining_balance' => 0.00,
            'status' => 'completed',
        ]);

        // Search by name
        Livewire::test(Deposits::class)
            ->set('search', 'Alice')
            ->assertSee('Alice Wonder')
            ->assertDontSee('Bob Builder');

        // Filter by completed status
        Livewire::test(Deposits::class)
            ->set('status_filter', 'completed')
            ->assertSee('Bob Builder')
            ->assertDontSee('Alice Wonder');
    }
}
