<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CustomItem;
use App\Models\Warranty;
use App\Livewire\Pos;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CustomItemAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['username' => 'testposadmin'],
            [
                'name' => 'Test POS Admin',
                'user_type' => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password')
            ]
        );
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        foreach ([
            'sales:read', 'sales:create', 'sales:update', 'sales:delete',
            'pos:read',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $role->syncPermissions(Permission::all());
        $this->user->assignRole($role);
    }

    public function test_custom_item_record_usage_and_search()
    {
        CustomItem::recordUsage('Screen Replacement OLED', 120.00, 70.00, null, '30 days warranty', true);
        CustomItem::recordUsage('Screen Cleaning & Polish', 25.00, 5.00, null, null, false);
        CustomItem::recordUsage('Battery Replacement iPhone 13', 85.00, 40.00, null, '6 months warranty', true);

        // Search for 'screen'
        $results = CustomItem::searchSuggestions('Screen', 10);
        $this->assertCount(2, $results);
        $this->assertEquals('Screen Replacement OLED', $results->first()->name);

        // Recording usage again increments count and updates details
        CustomItem::recordUsage('Screen Cleaning & Polish', 30.00, 5.00);
        $cleaned = CustomItem::where('name', 'Screen Cleaning & Polish')->first();
        $this->assertEquals(2, $cleaned->usage_count);
        $this->assertEquals(30.00, (float) $cleaned->price);

        // Search ranking: highest usage count comes first
        $results2 = CustomItem::searchSuggestions('Screen', 10);
        $this->assertEquals('Screen Cleaning & Polish', $results2->first()->name);
    }

    public function test_pos_livewire_suggests_and_selects_custom_item()
    {
        $warranty = Warranty::create([
            'name' => '3 Months Warranty',
            'description' => 'Covers manufacturer defects'
        ]);

        CustomItem::create([
            'name' => 'Laptop Fan Cleaning',
            'price' => 45.00,
            'purchase_price' => 10.00,
            'warranty_id' => $warranty->id,
            'warranty_description' => '7 days service guarantee',
            'is_vatable' => true,
            'usage_count' => 5,
            'last_used_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Pos::class)
            ->set('custom_item_name', 'Laptop')
            ->assertSet('show_custom_suggestions', true)
            ->assertCount('custom_item_suggestions', 1)
            ->call('selectCustomSuggestion', CustomItem::where('name', 'Laptop Fan Cleaning')->first()->id)
            ->assertSet('custom_item_name', 'Laptop Fan Cleaning')
            ->assertSet('custom_price', 45.0)
            ->assertSet('custom_purchase_price', 10.0)
            ->assertSet('custom_warranty_id', $warranty->id)
            ->assertSet('custom_warranty_description', '7 days service guarantee')
            ->assertSet('show_custom_suggestions', false);
    }

    public function test_adding_new_custom_item_saves_it_for_future_autocomplete()
    {
        Livewire::actingAs($this->user)
            ->test(Pos::class)
            ->set('custom_item_name', 'Thermal Paste Replacement')
            ->set('custom_price', 35.00)
            ->set('custom_purchase_price', 8.00)
            ->set('custom_quantity', 1)
            ->call('addCustomItem');

        $this->assertDatabaseHas('custom_items', [
            'name' => 'Thermal Paste Replacement',
            'price' => 35.00,
            'purchase_price' => 8.00,
            'usage_count' => 1,
        ]);
    }
}
