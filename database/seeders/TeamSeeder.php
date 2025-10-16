<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Équipe Commerciale',
                'description' => 'Équipe dédiée à la vente et au développement commercial',
            ],
            [
                'name' => 'Équipe Marketing',
                'description' => 'Équipe responsable du marketing et de la communication',
            ],
            [
                'name' => 'Équipe Support',
                'description' => 'Équipe de support client et technique',
            ],
            [
                'name' => 'Équipe Management',
                'description' => 'Équipe de direction et de management',
            ],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
