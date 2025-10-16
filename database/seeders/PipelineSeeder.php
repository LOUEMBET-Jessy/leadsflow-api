<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class PipelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user or create one
        $admin = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$admin) {
            $admin = User::first();
        }

        // Create default pipeline
        $pipeline = Pipeline::create([
            'name' => 'Pipeline de Vente Standard',
            'description' => 'Pipeline par défaut pour la gestion des leads',
            'is_default' => true,
            'created_by_user_id' => $admin->id,
        ]);

        // Create pipeline stages
        $stages = [
            [
                'name' => 'Premier Contact',
                'order' => 1,
                'color_code' => '#3B82F6',
            ],
            [
                'name' => 'Découverte',
                'order' => 2,
                'color_code' => '#8B5CF6',
            ],
            [
                'name' => 'Proposition',
                'order' => 3,
                'color_code' => '#F59E0B',
            ],
            [
                'name' => 'Négociation',
                'order' => 4,
                'color_code' => '#EF4444',
            ],
            [
                'name' => 'Fermeture',
                'order' => 5,
                'color_code' => '#10B981',
            ],
        ];

        foreach ($stages as $stage) {
            PipelineStage::create([
                'pipeline_id' => $pipeline->id,
                'name' => $stage['name'],
                'order' => $stage['order'],
                'color_code' => $stage['color_code'],
            ]);
        }

        // Create additional pipeline for marketing
        $marketingPipeline = Pipeline::create([
            'name' => 'Pipeline Marketing',
            'description' => 'Pipeline spécialisé pour les leads marketing',
            'is_default' => false,
            'created_by_user_id' => $admin->id,
        ]);

        $marketingStages = [
            [
                'name' => 'Lead Généré',
                'order' => 1,
                'color_code' => '#3B82F6',
            ],
            [
                'name' => 'Qualifié Marketing',
                'order' => 2,
                'color_code' => '#8B5CF6',
            ],
            [
                'name' => 'Transmis Commercial',
                'order' => 3,
                'color_code' => '#F59E0B',
            ],
        ];

        foreach ($marketingStages as $stage) {
            PipelineStage::create([
                'pipeline_id' => $marketingPipeline->id,
                'name' => $stage['name'],
                'order' => $stage['order'],
                'color_code' => $stage['color_code'],
            ]);
        }
    }
}
