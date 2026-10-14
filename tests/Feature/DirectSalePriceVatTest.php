<?php

namespace Tests\Feature;

use App\Livewire\Pos;
use App\Livewire\Sales\SaleEdit;
use App\Models\CashRegisterSession;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirectSalePriceVatTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_vat_and_profit_are_saved_consistently_when_creating_and_editing_a_sale(): void
    {
        $user = User::create(['name' => 'VAT check', 'username' => 'vatcheck', 'password' => bcrypt('password'), 'user_type' => 'admin']);
        foreach (['pos:read', 'pos:create', 'sales:read', 'sales:create', 'sales:update'] as $name) {
            $user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        }
        $this->actingAs($user);
        Shop::create(['name' => 'Receipt check', 'country' => 'France', 'gst_percent' => 20, 'currency' => ['code' => 'EUR', 'symbol' => '€'], 'settings' => ['language' => 'en']]);
        PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Cash', 'is_active' => true]);
        CashRegisterSession::create(['user_id' => $user->id, 'opened_at' => now(), 'opening_balance' => 0, 'status' => 'open']);

        $items = [
            'vat' => $this->item('VAT item', 10, 3, true),
            'nonvat' => $this->item('Non-VAT item', 5, 2, false),
        ];
        Livewire::test(Pos::class)->set('sale_items', $items)->assertSet('gst', '2.00')->assertSet('subtotal', '13.00')->assertSet('grand_total', '15.00')->call('sale')->assertHasNoErrors();
        $sale = Sale::with('saleItems')->sole();
        $this->assertSame('2.00', $sale->gst);
        $this->assertSame('15.00', $sale->total_price);
        $this->assertSame('8.00', $sale->profit);
        $this->assertEquals(8, $sale->saleItems->sum('profit'));
        $rows = \App\Services\SaleReceiptAmounts::forSale($sale);
        $this->assertEquals(['unit_net' => 8, 'vat' => 2, 'total' => 10], $rows[0]);
        $this->assertEquals(['unit_net' => 5, 'vat' => 0, 'total' => 5], $rows[1]);
        foreach (['en' => 'Unit excl.', 'fr' => 'Prix HT'] as $locale => $heading) {
            app()->setLocale($locale);
            $receipt = view('prints.sale-receipt', ['sale' => $sale, 'shop' => Shop::first()])->render();
            $this->assertStringContainsString($heading, $receipt);
            $this->assertStringContainsString('8.00', $receipt);
            $this->assertStringContainsString('2.00', $receipt);
        }
        app()->setLocale('en');

        $editor = Livewire::test(SaleEdit::class, ['sale_id' => $sale->id]);
        $key = 'custom_' . $sale->saleItems->firstWhere('item_name', 'VAT item')->id;
        $editor->set("sale_items.$key.discount", 2)->assertSet('gst', '1.60')->assertSet('subtotal', '13.40')->assertSet('grand_total', '13.00')->call('updateSale')->assertHasNoErrors();
        $sale->refresh()->load('saleItems');
        $this->assertSame('1.60', $sale->gst);
        $this->assertSame('13.00', $sale->total_price);
        $this->assertSame('6.40', $sale->profit);
        $this->assertEquals(6.4, $sale->saleItems->sum('profit'));
        $this->assertEquals(6.4, Sale::sum('profit'));
        $html = view('prints.sale-receipt-a4', ['sale' => $sale, 'shop' => Shop::first()])->render();
        $this->assertStringContainsString('8.00', $html);
        $this->assertStringContainsString('1.60', $html);
    }

    public function test_line_rounding_keeps_tax_and_profit_totals_consistent(): void
    {
        foreach ([Pos::class, SaleEdit::class] as $class) {
            $component = new $class;
            $component->gst_percent = 20;
            $component->sale_items = [$this->item('Tiny A', 0.03, 0, true), $this->item('Tiny B', 0.03, 0, true)];
            (new \ReflectionMethod($class, 'calculate'))->invoke($component);
            $this->assertSame('0.02', $component->gst);
            $this->assertSame('0.06', $component->grand_total);
        }
    }

    private function item(string $name, float $price, float $cost, bool $vat): array
    {
        return ['product_id' => null, 'product_variation_id' => null, 'item_name' => $name, 'price' => $price, 'purchase_price' => $cost, 'quantity' => 1, 'discount' => 0, 'total' => $price, 'is_vatable' => $vat];
    }
}
