<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\CurrencyValue;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        
        User::make([
            'name' => 'ADMIN',
            'email' => 'soporte@tecambiocash.com',
            'password' => Hash::make('Tecambiocash2023*CM'),
            'role' => 'ADMIN',
            'role_id' => 0,
            'state' => "ACTIVE"
        ])->save();
        
        CurrencyValue::make(['name' => 'TCS', 'value' => 1])->save();
        CurrencyValue::make(['name' => 'Paypal', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Advcash', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Bancolombia', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Banesco Venezuela', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Zelle', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Zinli', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Nequi', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Banco General', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Airtm', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Skrill', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Banco Pichincha', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Perfec Money', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Payeer', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Interbank', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Produbanco', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Banesco', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'BCP', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Scotibank', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Banesco Panama', 'value' => 0])->save();
        CurrencyValue::make(['name' => 'Banco Venezuela', 'value' => 0])->save();
    }
}
