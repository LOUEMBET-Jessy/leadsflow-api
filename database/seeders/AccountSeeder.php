<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        Account::create([
            'name' => 'AGL Gabon',
            'slug' => 'agl-gabon',
            'domain' => 'agl-gabon.leadflow.com',
            'plan' => 'premium',
            'settings' => [
                'timezone' => 'Africa/Libreville',
                'currency' => 'XAF',
                'language' => 'fr',
                'features' => [
                    'automations' => true,
                    'email_sequences' => true,
                    'integrations' => true,
                    'advanced_analytics' => true,
                ]
            ],
            'is_active' => true,
            'trial_ends_at' => now()->addDays(30),
        ]);
    }
}
