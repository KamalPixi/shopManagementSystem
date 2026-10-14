<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\CashRegisterSession;
use App\Services\PrinterManagerService;
use App\Livewire\Settings\PrinterSettings;
use App\Livewire\Pos;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrinterManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $role = Role::firstOrCreate(['name' => 'admin']);
        $permissions = [
            'settings:read',
            'settings:update',
            'pos:read',
            'pos:create',
            'sales:read',
            'sales:create',
            'cash-register:read',
            'cash-register:create',
        ];

        foreach ($permissions as $p) {
            $perm = Permission::firstOrCreate(['name' => $p]);
            $role->givePermissionTo($perm);
        }

        $this->user = User::firstOrCreate(
            ['username' => 'printeradmin'],
            [
                'name' => 'Printer Admin',
                'email' => 'printeradmin@test.com',
                'user_type' => 'admin',
                'password' => Hash::make('password'),
            ]
        );
        $this->user->assignRole($role);

        $this->shop = Shop::firstOrCreate(['id' => 1], [
            'name' => 'Tech Parisien',
            'address' => '123 Rue de Paris',
            'city' => 'Paris',
            'state' => 'Ile-de-France',
            'postal_code' => '75001',
            'country' => 'France',
            'contact_no' => '0123456789',
            'email' => 'shop@test.com',
            'currency' => ['code' => 'EUR', 'symbol' => '€'],
            'settings' => [],
        ]);

        PaymentMethod::firstOrCreate(['code' => 'cash'], [
            'name' => 'Cash',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_guest_cannot_access_printer_settings_page(): void
    {
        $response = $this->get(route('printers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_user_can_access_printer_settings_page(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('printers.index'));
        $response->assertStatus(200);
        $response->assertSee(__('Printer Management'));
    }

    public function test_printer_manager_service_returns_defaults(): void
    {
        $service = app(PrinterManagerService::class);
        $settings = $service->getSettings();

        $this->assertArrayHasKey('pos', $settings);
        $this->assertArrayHasKey('a4', $settings);
        $this->assertArrayHasKey('barcode', $settings);
        $this->assertEquals('80mm', $settings['pos']['paper_width']);
        $this->assertTrue($settings['pos']['cut_paper']);
    }

    public function test_can_save_printer_settings_via_livewire(): void
    {
        $this->actingAs($this->user);

        Livewire::test(PrinterSettings::class)
            ->set('pos_printer_name', 'XP-80C Thermal')
            ->set('pos_connection_type', 'windows')
            ->set('pos_paper_width', '80mm')
            ->set('pos_open_drawer', true)
            ->set('pos_cut_paper', true)
            ->set('pos_auto_print_on_sale', true)
            ->set('a4_printer_name', 'HP LaserJet Pro M404')
            ->set('a4_auto_print', true)
            ->set('barcode_printer_name', 'TSC TE244')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('Printer configuration updated successfully.'));

        // Verify stored in Shop settings
        $service = app(PrinterManagerService::class);
        $stored = $service->getSettings();

        $this->assertEquals('XP-80C Thermal', $stored['pos']['printer_name']);
        $this->assertEquals('windows', $stored['pos']['connection_type']);
        $this->assertEquals('80mm', $stored['pos']['paper_width']);
        $this->assertTrue($stored['pos']['auto_print_on_sale']);
        $this->assertEquals('HP LaserJet Pro M404', $stored['a4']['printer_name']);
        $this->assertTrue($stored['a4']['auto_print']);
        $this->assertEquals('TSC TE244', $stored['barcode']['printer_name']);
    }

    public function test_detect_system_printers_does_not_crash(): void
    {
        $service = app(PrinterManagerService::class);
        $printers = $service->detectSystemPrinters();

        $this->assertIsArray($printers);
    }

    public function test_receipt_submission_failure_is_reported_instead_of_success(): void
    {
        $connector = new \App\Printing\WindowsSpoolerConnector('XP-80C (USB)', function ($name, $bytes) {
            $this->assertSame('XP-80C (USB)', $name);
            $this->assertStringContainsString('TEST RECEIPT', $bytes);
            throw new \RuntimeException('Windows print queue is unavailable');
        });
        $service = \Mockery::mock(PrinterManagerService::class)->makePartial();
        $service->shouldReceive('getPosPrinterConnector')->once()->andReturn([
            'success' => true, 'printer' => new \Mike42\Escpos\Printer($connector),
            'target' => 'XP-80C (USB)', 'settings' => $service->getDefaultSettings()['pos'],
        ]);

        $result = $service->testPosPrint();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Windows print queue is unavailable', $result['message']);
    }

    public function test_native_a4_print_rejection_is_reported(): void
    {
        config(['nativephp-internal.api_url' => 'http://127.0.0.1:49999/']);
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response('', 500)]);
        $path = tempnam(sys_get_temp_dir(), 'a4-test-');
        try {
            $result = app(PrinterManagerService::class)->printA4Document($path, "Office (USB)", '<html><body>A4 #test</body></html>');
            $this->assertFalse($result['success']);
            $this->assertStringContainsString('rejected', $result['message']);
            \Illuminate\Support\Facades\Http::assertSent(fn ($request) =>
                $request['printer'] === 'Office (USB)' &&
                $request['html'] === rawurlencode('<html><body>A4 #test</body></html>') &&
                $request['settings']['pageSize'] === 'A4');
        } finally {
            unlink($path);
        }
    }

    public function test_test_print_does_not_run_when_settings_cannot_be_saved(): void
    {
        $service = \Mockery::mock(PrinterManagerService::class)->makePartial();
        $service->shouldReceive('detectSystemPrinters')->andReturn([]);
        $service->shouldReceive('saveSettings')->once()->andReturn(false);
        $service->shouldReceive('testPosPrint')->never();
        $this->app->instance(PrinterManagerService::class, $service);
        $this->actingAs($this->user);
        Livewire::test(PrinterSettings::class)->call('testPosPrint')
            ->assertSee(__('Failed to save printer settings.'));
    }

    public function test_network_printer_requires_a_valid_ip_address(): void
    {
        $this->actingAs($this->user);
        Livewire::test(PrinterSettings::class)
            ->set('pos_connection_type', 'network')
            ->set('pos_ip_address', '')
            ->call('save')->assertHasErrors(['pos_ip_address']);
    }

    public function test_pos_auto_print_runs_safely_on_sale(): void
    {
        $this->actingAs($this->user);

        // Configure auto-print enabled with test printer name
        $service = app(PrinterManagerService::class);
        $service->saveSettings([
            'pos' => [
                'printer_name' => 'XP-80C',
                'connection_type' => 'windows',
                'paper_width' => '80mm',
                'open_drawer' => true,
                'cut_paper' => true,
                'auto_print_on_sale' => true,
            ],
            'a4' => ['printer_name' => 'HP LaserJet', 'auto_print' => false],
            'barcode' => ['printer_name' => 'TSC'],
        ]);

        // Open register session for today
        CashRegisterSession::create([
            'user_id' => $this->user->id,
            'opening_balance' => 100.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $customItem = [
            'key' => 'custom_1',
            'product_id' => null,
            'product_variation_id' => null,
            'item_name' => 'Repair Service Test',
            'price' => 50.00,
            'purchase_price' => 0.00,
            'discount' => 0,
            'quantity' => 1,
            'total' => 50.00,
            'is_vatable' => true,
            'show_warranty_on_receipt' => false,
            'show_serial_on_receipt' => false,
            'is_custom' => true,
        ];

        Livewire::test(Pos::class)
            ->set('sale_items', ['custom_1' => $customItem])
            ->set('payment_method', 'cash')
            ->set('customer_name', 'Jean Dupont')
            ->call('sale')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sales', [
            'total_price' => 50.00,
        ]);
    }
}
