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


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate([
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
        Brand::firstOrCreate(['name' => 'default', 'description' => 'default category']);
        Supplier::firstOrCreate([
            'name' => 'default',
        ], [
            'email' => 'default@domain.com',
        ]);
    }
}
