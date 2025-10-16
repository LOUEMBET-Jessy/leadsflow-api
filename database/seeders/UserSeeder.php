<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::first();

        // Admin user
        User::create([
            'account_id' => $account->id,
            'name' => 'Admin AGL',
            'email' => 'admin@agl-gabon.com',
            'password' => Hash::make('password123'),
            'role' => 'Admin',
            'phone' => '+241 01 23 45 67',
            'is_active' => true,
        ]);

        // Manager user
        User::create([
            'account_id' => $account->id,
            'name' => 'Manager Commercial',
            'email' => 'manager@agl-gabon.com',
            'password' => Hash::make('password123'),
            'role' => 'Manager',
            'phone' => '+241 01 23 45 68',
            'is_active' => true,
        ]);

        // Commercial users
        User::create([
            'account_id' => $account->id,
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@agl-gabon.com',
            'password' => Hash::make('password123'),
            'role' => 'Commercial',
            'phone' => '+241 01 23 45 69',
            'is_active' => true,
        ]);

        User::create([
            'account_id' => $account->id,
            'name' => 'Marie Martin',
            'email' => 'marie.martin@agl-gabon.com',
            'password' => Hash::make('password123'),
            'role' => 'Commercial',
            'phone' => '+241 01 23 45 70',
            'is_active' => true,
        ]);

        // Marketing user
        User::create([
            'account_id' => $account->id,
            'name' => 'Sophie Marketing',
            'email' => 'sophie@agl-gabon.com',
            'password' => Hash::make('password123'),
            'role' => 'Marketing',
            'phone' => '+241 01 23 45 71',
            'is_active' => true,
        ]);

        // GestLead user
        User::create([
            'account_id' => $account->id,
            'name' => 'Paul GestLead',
            'email' => 'paul@agl-gabon.com',
            'password' => Hash::make('password123'),
            'role' => 'GestLead',
            'phone' => '+241 01 23 45 72',
            'is_active' => true,
        ]);
    }
}