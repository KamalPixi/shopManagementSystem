<?php

namespace Tests\Feature;

use App\Livewire\Stocks\StockAdd;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SerialNumberValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'stock:create', 'guard_name' => 'web']);
        $user = \App\Models\User::factory()->create(['username' => 'serial-test', 'user_type' => 'staff']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);
    }

    public function test_existing_product_and_variation_serials_are_rejected_on_blur(): void
    {
        $product = Product::create(['name' => 'Phone', 'sku' => 'phone', 'serial_no' => 'SN-1', 'price' => 10]);
        $product->variations()->create(['variation_name' => 'Blue', 'serial_no' => 'SN-2', 'price' => 10]);

        foreach ([' sn-1 ', 'SN-2'] as $serial) {
            Livewire::test(StockAdd::class)->set('serial_no', $serial)->assertHasErrors('serial_no');
        }
    }

    public function test_duplicate_serials_in_variation_rows_are_rejected(): void
    {
        Livewire::test(StockAdd::class)
            ->set('has_variations', true)
            ->set('variations', [['serial_no' => 'SN-3'], ['serial_no' => 'sn-3']])
            ->set('variations.1.serial_no', 'sn-3')
            ->assertHasErrors(['variations.0.serial_no', 'variations.1.serial_no']);
    }

    public function test_blank_serials_are_normalized_and_allowed(): void
    {
        Livewire::test(StockAdd::class)->set('serial_no', '   ')
            ->assertSet('serial_no', null)->assertHasNoErrors('serial_no');
    }
}
