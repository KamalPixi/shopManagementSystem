<?php

namespace Tests\Feature;

use App\Livewire\Sales\Sales;
use App\Livewire\Sales\SaleShow;
use App\Models\CashRegisterSession;
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

class MultiLanguageSaleReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Staff Member',
            'username' => 'staff1',
            'password' => bcrypt('secret'),
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
            'gst_percent' => 20,
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

        $product = Product::create([
            'name' => 'Wireless Mouse',
            'sku' => 'MOUSE-001',
            'price' => 25.00,
            'cost_price' => 15.00,
            'is_active' => true,
        ]);

        $this->sale = Sale::create([
            'receipt_no' => 'TP-999001',
            'total_price' => 25.00,
            'discount' => 0.00,
            'gst' => 4.17,
            'profit' => 10.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
        ]);

        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $product->id,
            'item_name' => $product->name,
            'quantity' => 1,
            'purchase_price' => 15.00,
            'price' => 25.00,
            'discount' => 0.00,
            'total' => 25.00,
            'profit' => 10.00,
            'is_vatable' => true,
        ]);
    }

    public function test_sale_service_generates_locale_specific_receipt_and_a4_urls(): void
    {
        $service = new SaleService();

        $enUrl = $service->generateSaleReceipt($this->sale->id, 'en');
        $frUrl = $service->generateSaleReceipt($this->sale->id, 'fr');
        $enA4Url = $service->generateSaleReceiptA4($this->sale->id, 'en');
        $frA4Url = $service->generateSaleReceiptA4($this->sale->id, 'fr');

        $this->assertStringContainsString('sale_receipt_TP-999001_en.pdf', $enUrl);
        $this->assertStringContainsString('sale_receipt_TP-999001_fr.pdf', $frUrl);
        $this->assertStringContainsString('sale_receipt_a4_TP-999001_en.pdf', $enA4Url);
        $this->assertStringContainsString('sale_receipt_a4_TP-999001_fr.pdf', $frA4Url);

        // Verify that global locale is restored after execution
        $this->assertSame('en', app()->getLocale());
    }

    public function test_sales_index_component_dispatches_print_events_for_both_languages(): void
    {
        Livewire::test(Sales::class)
            ->call('printSaleReceipt', $this->sale->id, 'en')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, '_en.pdf');
            });

        Livewire::test(Sales::class)
            ->call('printSaleReceipt', $this->sale->id, 'fr')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, '_fr.pdf');
            });

        Livewire::test(Sales::class)
            ->call('printSaleReceiptA4', $this->sale->id, 'en')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, 'a4_TP-999001_en.pdf');
            });

        Livewire::test(Sales::class)
            ->call('printSaleReceiptA4', $this->sale->id, 'fr')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, 'a4_TP-999001_fr.pdf');
            });
    }

    public function test_sales_index_view_renders_en_and_fr_buttons_for_all_print_types(): void
    {
        Livewire::test(Sales::class)
            ->assertSeeHtml("wire:click=\"printSaleReceiptPos('{$this->sale->id}', 'en')\"")
            ->assertSeeHtml("wire:click=\"printSaleReceiptPos('{$this->sale->id}', 'fr')\"")
            ->assertSeeHtml("wire:click=\"printSaleReceipt('{$this->sale->id}', 'en')\"")
            ->assertSeeHtml("wire:click=\"printSaleReceipt('{$this->sale->id}', 'fr')\"")
            ->assertSeeHtml("wire:click=\"printSaleReceiptA4('{$this->sale->id}', 'en')\"")
            ->assertSeeHtml("wire:click=\"printSaleReceiptA4('{$this->sale->id}', 'fr')\"");
    }

    public function test_sale_show_component_supports_both_languages(): void
    {
        Livewire::test(SaleShow::class, ['sale_id' => $this->sale->id])
            ->call('printSaleReceipt', 'en')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, '_en.pdf');
            });

        Livewire::test(SaleShow::class, ['sale_id' => $this->sale->id])
            ->call('printSaleReceipt', 'fr')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, '_fr.pdf');
            });

        Livewire::test(SaleShow::class, ['sale_id' => $this->sale->id])
            ->call('printSaleReceiptA4', 'en')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, 'a4_TP-999001_en.pdf');
            });

        Livewire::test(SaleShow::class, ['sale_id' => $this->sale->id])
            ->call('printSaleReceiptA4', 'fr')
            ->assertDispatched('print-pdf', function ($name, $params) {
                $url = $params[0]['url'] ?? ($params['url'] ?? '');
                return str_contains($url, 'a4_TP-999001_fr.pdf');
            });
    }
}
