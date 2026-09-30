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
use App\Models\SystemMeta;
use App\Models\PaymentMethod;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Cash', 'code' => 'cash', 'sort_order' => 1],
            ['name' => 'Card', 'code' => 'card', 'sort_order' => 2],
            ['name' => 'Bank Transfer', 'code' => 'bank_transfer', 'sort_order' => 3],
            ['name' => 'Other', 'code' => 'other', 'sort_order' => 4],
        ] as $method) {
            PaymentMethod::firstOrCreate(['code' => $method['code']], $method);
        }
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
            'name' => 'TECH PARISIEN', 
            'address' => '33 rue',
            'city' => 'Brézin',
            'state' => 'Paris,',
            'postal_code' => '75014',
            'country' => 'France', 
            'gst_percent' => 0,
            'currency' => ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
            'timezone' => 'Europe/Paris',
            'settings' => [
                'invoice_legal_text' => "**SAS au capital de 2 010 €**\n{address}\n**SIREN** : 109 915 405\nTVA intracommunautaire: FR75 109915405",
            ],
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

            'supplier:create',
            'supplier:read',
            'supplier:update',
            'supplier:delete',

            'customer:create',
            'customer:read',
            'customer:update',
            'customer:delete',

            'money-movements:create',
            'money-movements:read',
            'money-movements:delete',

            'cash-register:create',
            'cash-register:read',
            'cash-register:update',

            'audit-logs:read',

            'dashboard:read',
            'pos:read',
            'reports:read',
            'db-backup:read',
            'db-backup:create',
            'db-backup:restore',
            'payment-methods:read',
            'payment-methods:create',
            'payment-methods:update',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
        $roleAdmin->syncPermissions(Permission::get());
        $userAdmin->assignRole($roleAdmin);

        // Seed default money movement categories
        $defaultCategories = [
            // Cash In
            ['name' => "Apport d'un associé", 'type' => 'in', 'description' => "Apport de fonds par un associé (Partner Contribution)"],
            ['name' => 'Paiement client', 'type' => 'in', 'description' => 'Paiement direct reçu d\'un client (Customer Payment)'],
            ['name' => "Règlement d'un impayé", 'type' => 'in', 'description' => "Règlement d'une créance ou facture impayée (Previous Due Payment)"],
            ['name' => 'Ajustement de caisse', 'type' => 'in', 'description' => 'Ajustement positif du solde de caisse (Cash Adjustment)'],
            
            // Cash Out
            ['name' => 'Achat de produit', 'type' => 'out', 'description' => 'Achat de marchandises ou produits (Product Purchase)'],
            ['name' => 'Paiement fournisseur', 'type' => 'out', 'description' => 'Règlement de facture fournisseur (Supplier Payment)'],
            ['name' => 'Dépense du magasin', 'type' => 'out', 'description' => "Frais et charges d'exploitation du magasin (Shop Expense)"],
            ['name' => 'Remboursement client', 'type' => 'out', 'description' => 'Remboursement ou avoir client en espèces (Customer Refund)'],
            ['name' => "Retrait d'un associé", 'type' => 'out', 'description' => "Retrait de fonds par un associé (Partner Withdrawal)"],
            ['name' => 'Dépôt bancaire', 'type' => 'out', 'description' => 'Versement des espèces en banque (Bank Deposit)'],
            ['name' => 'Petite caisse', 'type' => 'out', 'description' => 'Dépenses courantes de petite caisse (Petty Cash)'],

            // Both (In & Out)
            ['name' => 'Autre', 'type' => 'both', 'description' => 'Autre opération de caisse (Other)'],
        ];

        foreach ($defaultCategories as $cat) {
            \App\Models\MoneyCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['type' => $cat['type'], 'description' => $cat['description'], 'created_by' => $userAdmin->id]
            );
        }

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
