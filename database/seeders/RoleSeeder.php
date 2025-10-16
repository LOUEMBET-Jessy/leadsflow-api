<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrateur système avec tous les droits',
            ],
            [
                'name' => 'manager',
                'description' => 'Manager avec droits de gestion d\'équipe',
            ],
            [
                'name' => 'sales',
                'description' => 'Commercial avec droits de gestion des leads',
            ],
            [
                'name' => 'marketing',
                'description' => 'Marketing avec droits de création de campagnes',
            ],
            [
                'name' => 'lead_manager',
                'description' => 'Gestionnaire de leads spécialisé',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
