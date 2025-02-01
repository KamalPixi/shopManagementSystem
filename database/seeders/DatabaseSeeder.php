<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Warranty;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userAdmin = User::firstOrCreate([
            'username' => 'admin',
        ],[
            'name' => 'Admin',
            'user_type' => 'admin',
            'password' => Hash::make('password')
        ]);

        $warranties = [
            ['name' => '6 Months', 'duration' => '6 months'],
            ['name' => '1 Year', 'duration' => '1 year'],
            ['name' => '2 Years', 'duration' => '2 years'],
        ];
        foreach ($warranties as $warranty) {
            Warranty::firstOrCreate(
                ['name' => $warranty['name']],
                ['description' => "This warranty is valid for {$warranty['duration']} from the date of sale."]
            );
        }

        Category::firstOrCreate(['name' => 'default' , 'description' => 'default category']);
        Brand::firstOrCreate(['name' => 'default', 'description' => 'default brand']);
        Supplier::firstOrCreate([
            'name' => 'default',
        ], [
            'email' => 'default@domain.com',
        ]);
        Shop::firstOrCreate([
            'name' => 'GreenPixi', 
            'country' => 'Malaysia', 
            'gst_percent' => 6,
            'currency' => ['code' => 'MYR', 'symbol' => 'RM', 'name' => 'Malaysian Ringgit'],
        ]);

        // create role and permissions & assign admin as admin role
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'staff']);
        $permissions = [
            'users:create',
            'users:read',
            'users:update',
            'users:delete',

            'stock:create',
            'stock:read',
            'stock:update',
            'stock:delete',

            'sales:create',
            'sales:read',
            'sales:update',
            'sales:delete',

            'warranty-send:create',
            'warranty-send:read',
            'warranty-send:update',
            'warranty-send:delete',

            'brand:create',
            'brand:read',
            'brand:update',
            'brand:delete',

            'category:create',
            'category:read',
            'category:update',
            'category:delete',

            'warranties:create',
            'warranties:read',
            'warranties:update',
            'warranties:delete',

            'settings:create',
            'settings:read',
            'settings:update',
            'settings:delete',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
        $roleAdmin->syncPermissions(Permission::get());
        $userAdmin->assignRole($roleAdmin);

        $s = SystemMeta::first();
        if ($s) {
            $s->update([
                'is_db_seeded' => true
            ]);
        }else {
            SystemMeta::create([
                'is_installed' => true,
                'is_db_seeded' => true,
            ]);
        }
    }
}
