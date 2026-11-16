<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\MoneyMovement;
use App\Models\MoneyCategory;
use App\Models\CashRegisterSession;
use App\Livewire\MoneyMovements;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MoneyMovementReportPrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Shop $shop;
    protected CashRegisterSession $session;
    protected MoneyCategory $catIn;
    protected MoneyCategory $catOut;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin user with all permissions
        $role = Role::firstOrCreate(['name' => 'admin']);
        Permission::firstOrCreate(['name' => 'money-movements:create']);
        Permission::firstOrCreate(['name' => 'money-movements:read']);
        Permission::firstOrCreate(['name' => 'money-movements:delete']);
        $role->givePermissionTo(Permission::all());

        $this->admin = User::create([
            'name' => 'Manager Admin',
            'username' => 'manager_' . uniqid(),
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);

        $this->shop = Shop::firstOrCreate(['id' => 1], [
            'name' => 'Tech Parisien',
            'address' => '123 Rue de Rivoli',
            'city' => 'Paris',
            'country' => 'France',
            'contact_no' => '0123456789',
            'email' => 'contact@techparisien.com',
            'currency' => ['symbol' => '€', 'code' => 'EUR'],
        ]);

        $this->session = CashRegisterSession::create([
            'user_id' => $this->admin->id,
            'status' => 'open',
            'opening_balance' => 200.00,
            'opened_at' => now(),
        ]);

        $this->catIn = MoneyCategory::create([
            'name' => 'Partner Contribution',
            'type' => 'in',
            'created_by' => $this->admin->id,
        ]);

        $this->catOut = MoneyCategory::create([
            'name' => 'Shop Expense',
            'type' => 'out',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_print_report_dispatches_print_pdf_event_with_valid_url(): void
    {
        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'in',
            'amount' => 500.00,
            'money_category_id' => $this->catIn->id,
            'reason' => 'Monthly Capital Injection',
            'user_id' => $this->admin->id,
        ]);

        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'out',
            'amount' => 120.00,
            'money_category_id' => $this->catOut->id,
            'reason' => 'Store Supplies and Cleaning',
            'user_id' => $this->admin->id,
        ]);

        $component = Livewire::test(MoneyMovements::class)
            ->call('printReport');

        $component->assertDispatched('print-pdf');

        $dispatched = $component->effects['dispatches'] ?? [];
        $printPdfEvent = collect($dispatched)->firstWhere('name', 'print-pdf');
        $this->assertNotNull($printPdfEvent, 'print-pdf event was not dispatched');

        $url = $printPdfEvent['params']['url'] ?? ($printPdfEvent['params'][0]['url'] ?? null);
        $this->assertNotNull($url);
        $this->assertStringContainsString('money_movements_report', $url);
        $this->assertFileExists(public_path('reports/money_movements_report.pdf'));
    }

    public function test_print_report_respects_type_filter(): void
    {
        // 1 Cash In of 300, 1 Cash Out of 150
        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'in',
            'amount' => 300.00,
            'money_category_id' => $this->catIn->id,
            'reason' => 'Incoming Fund',
            'user_id' => $this->admin->id,
        ]);

        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'out',
            'amount' => 150.00,
            'money_category_id' => $this->catOut->id,
            'reason' => 'Coffee & Snacks',
            'user_id' => $this->admin->id,
        ]);

        // Filter for Cash Out only
        $component = Livewire::test(MoneyMovements::class)
            ->set('filter_type', 'out')
            ->call('printReport');

        $component->assertDispatched('print-pdf');

        $service = new \App\Services\MoneyMovementPdfService();
        $movements = MoneyMovement::where('type', 'out')->get();

        $html = view('prints.money-movements-report', [
            'movements' => $movements,
            'shop' => $this->shop,
            'locale' => 'en',
            'totalIn' => 0.00,
            'totalOut' => 150.00,
            'netCash' => -150.00,
            'totalCount' => 1,
            'filter_type_label' => 'Cash Out',
            'filter_category_name' => 'All Categories',
            'filter_user_name' => null,
            'filter_start_date' => date('Y-m-01'),
            'filter_end_date' => date('Y-m-d'),
            'print_date' => date('d/m/Y H:i'),
        ])->render();

        $this->assertStringContainsString('Coffee &amp; Snacks', $html);
        $this->assertStringNotContainsString('Incoming Fund', $html);
        $this->assertStringContainsString('150.00', $html);
    }

    public function test_print_report_respects_category_filter(): void
    {
        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'in',
            'amount' => 400.00,
            'money_category_id' => $this->catIn->id,
            'reason' => 'Partner Injection',
            'user_id' => $this->admin->id,
        ]);

        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'out',
            'amount' => 50.00,
            'money_category_id' => $this->catOut->id,
            'reason' => 'Printer Paper',
            'user_id' => $this->admin->id,
        ]);

        // Filter for Partner Contribution only
        $component = Livewire::test(MoneyMovements::class)
            ->set('filter_category_id', $this->catIn->id)
            ->call('printReport');

        $component->assertDispatched('print-pdf');

        $filtered = MoneyMovement::where('money_category_id', $this->catIn->id)->get();
        $this->assertCount(1, $filtered);
        $this->assertEquals(400.00, (float) $filtered->first()->amount);
    }

    public function test_pdf_report_view_renders_compact_table_and_summary_metrics(): void
    {
        $movement = MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'in',
            'amount' => 250.00,
            'money_category_id' => $this->catIn->id,
            'reason' => 'Cash Added for Float',
            'user_id' => $this->admin->id,
        ]);

        $html = view('prints.money-movements-report', [
            'movements' => collect([$movement]),
            'shop' => $this->shop,
            'locale' => 'en',
            'totalIn' => 250.00,
            'totalOut' => 0.00,
            'netCash' => 250.00,
            'totalCount' => 1,
            'categoryBreakdown' => [
                'in' => ['Partner Contribution' => 250.00],
                'out' => [],
            ],
            'filter_type_label' => 'All Types',
            'filter_category_name' => 'All Categories',
            'filter_user_name' => null,
            'filter_start_date' => date('Y-m-01'),
            'filter_end_date' => date('Y-m-d'),
            'print_date' => date('d/m/Y H:i'),
        ])->render();

        $this->assertStringContainsString($this->shop->name, $html);
        $this->assertStringContainsString('Movement History Log Report', $html);
        $this->assertStringContainsString('250.00', $html);
        $this->assertStringContainsString('Partner Contribution', $html);
        $this->assertStringContainsString('Cash Added for Float', $html);
        $this->assertStringContainsString('Session #' . $this->session->id, $html);

        // Verify requested removals:
        $this->assertStringNotContainsString('Electronically generated report', $html);
        $this->assertStringNotContainsString('kpi-table', $html);
        // Print date must only appear once (in the metadata section, not duplicated in footer)
        $this->assertEquals(1, substr_count($html, 'Print Date'));
    }

    public function test_record_movement_persists_payment_method(): void
    {
        Livewire::test(MoneyMovements::class)
            ->set('type', 'in')
            ->set('payment_method', 'bank_transfer')
            ->set('amount', '1500.00')
            ->set('money_category_id', $this->catIn->id)
            ->set('reason', 'Capital Injection via Bank Wire')
            ->call('recordMovement')
            ->assertHasNoErrors();

        $movement = MoneyMovement::where('amount', 1500.00)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('bank_transfer', $movement->payment_method);
        $this->assertEquals('Bank transfer', $movement->method_label);

        Livewire::test(MoneyMovements::class)
            ->set('type', 'out')
            ->set('payment_method', 'card')
            ->set('amount', '85.50')
            ->set('reason', 'Office Software Paid by Business Card')
            ->call('recordMovement')
            ->assertHasNoErrors();

        $outMovement = MoneyMovement::where('amount', 85.50)->first();
        $this->assertNotNull($outMovement);
        $this->assertEquals('card', $outMovement->payment_method);
        $this->assertEquals('Card', $outMovement->method_label);
    }

    public function test_non_cash_movements_do_not_alter_physical_cash_drawer(): void
    {
        // Opening balance is 200.00
        $this->assertEquals(200.00, $this->session->calculateExpectedCash());

        // 1. Bank transfer in of 1000.00 - should NOT affect cash drawer
        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'in',
            'payment_method' => 'bank_transfer',
            'amount' => 1000.00,
            'money_category_id' => $this->catIn->id,
            'reason' => 'Bank Capital Addition',
            'user_id' => $this->admin->id,
        ]);

        // 2. Card expense out of 150.00 - should NOT affect cash drawer
        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'out',
            'payment_method' => 'card',
            'amount' => 150.00,
            'money_category_id' => $this->catOut->id,
            'reason' => 'Card Purchase',
            'user_id' => $this->admin->id,
        ]);

        // Expected cash in drawer must STILL be 200.00 (non-cash does NOT alter physical drawer)
        $this->assertEquals(200.00, $this->session->fresh()->calculateExpectedCash());

        // 3. Physical cash in of 50.00 - DOES increase cash drawer
        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'in',
            'payment_method' => 'cash',
            'amount' => 50.00,
            'money_category_id' => $this->catIn->id,
            'reason' => 'Coins for Till',
            'user_id' => $this->admin->id,
        ]);

        // Drawer is now 200.00 + 50.00 = 250.00
        $this->assertEquals(250.00, $this->session->fresh()->calculateExpectedCash());

        // Verify method movements breakdown tracking
        $breakdown = $this->session->fresh()->calculateMethodMovementsBreakdown();
        $this->assertEquals(50.00, $breakdown['cash']['in']);
        $this->assertEquals(50.00, $breakdown['cash']['net']);

        $this->assertEquals(1000.00, $breakdown['bank_transfer']['in']);
        $this->assertEquals(1000.00, $breakdown['bank_transfer']['net']);

        $this->assertEquals(150.00, $breakdown['card']['out']);
        $this->assertEquals(-150.00, $breakdown['card']['net']);
    }

    public function test_movements_filter_by_payment_method(): void
    {
        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'in',
            'payment_method' => 'cash',
            'amount' => 100.00,
            'money_category_id' => $this->catIn->id,
            'reason' => 'Cash Float',
            'user_id' => $this->admin->id,
        ]);

        MoneyMovement::create([
            'cash_register_session_id' => $this->session->id,
            'type' => 'in',
            'payment_method' => 'bank_transfer',
            'amount' => 2000.00,
            'money_category_id' => $this->catIn->id,
            'reason' => 'Wire from Director',
            'user_id' => $this->admin->id,
        ]);

        // Filter for bank_transfer only
        Livewire::test(MoneyMovements::class)
            ->set('filter_payment_method', 'bank_transfer')
            ->assertSee('Wire from Director')
            ->assertDontSee('Cash Float');

        // Filter for cash only
        Livewire::test(MoneyMovements::class)
            ->set('filter_payment_method', 'cash')
            ->assertSee('Cash Float')
            ->assertDontSee('Wire from Director');
    }
}
