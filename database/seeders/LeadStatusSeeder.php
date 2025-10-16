<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use Illuminate\Database\Seeder;

class LeadStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Nouveau',
                'color_code' => '#3B82F6',
                'is_final' => false,
                'order' => 1,
            ],
            [
                'name' => 'Contacté',
                'color_code' => '#8B5CF6',
                'is_final' => false,
                'order' => 2,
            ],
            [
                'name' => 'Qualifié',
                'color_code' => '#F59E0B',
                'is_final' => false,
                'order' => 3,
            ],
            [
                'name' => 'Négociation',
                'color_code' => '#EF4444',
                'is_final' => false,
                'order' => 4,
            ],
            [
                'name' => 'Gagné',
                'color_code' => '#10B981',
                'is_final' => true,
                'order' => 5,
            ],
            [
                'name' => 'Perdu',
                'color_code' => '#6B7280',
                'is_final' => true,
                'order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            LeadStatus::create($status);
        }
    }
}
