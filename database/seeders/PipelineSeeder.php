<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\Account;

class PipelineSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::first();

        // Pipeline de vente principal
        $salesPipeline = Pipeline::create([
            'account_id' => $account->id,
            'name' => 'Pipeline de Vente Principal',
            'description' => 'Pipeline principal pour la gestion des prospects commerciaux',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Étapes du pipeline de vente
        $stages = [
            ['name' => 'Nouveau', 'color' => '#3498db', 'order' => 1, 'is_final' => false],
            ['name' => 'Contacté', 'color' => '#f39c12', 'order' => 2, 'is_final' => false],
            ['name' => 'Qualification', 'color' => '#e67e22', 'order' => 3, 'is_final' => false],
            ['name' => 'Négociation', 'color' => '#e74c3c', 'order' => 4, 'is_final' => false],
            ['name' => 'Gagné', 'color' => '#27ae60', 'order' => 5, 'is_final' => true],
            ['name' => 'Perdu', 'color' => '#95a5a6', 'order' => 6, 'is_final' => true],
        ];

        foreach ($stages as $stageData) {
            $salesPipeline->stages()->create($stageData);
        }

        // Pipeline marketing
        $marketingPipeline = Pipeline::create([
            'account_id' => $account->id,
            'name' => 'Pipeline Marketing',
            'description' => 'Pipeline pour les leads marketing et les campagnes',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Étapes du pipeline marketing
        $marketingStages = [
            ['name' => 'Lead Marketing', 'color' => '#9b59b6', 'order' => 1, 'is_final' => false],
            ['name' => 'Qualifié', 'color' => '#f39c12', 'order' => 2, 'is_final' => false],
            ['name' => 'Nurturing', 'color' => '#e67e22', 'order' => 3, 'is_final' => false],
            ['name' => 'Prêt pour Vente', 'color' => '#27ae60', 'order' => 4, 'is_final' => true],
            ['name' => 'Non Qualifié', 'color' => '#95a5a6', 'order' => 5, 'is_final' => true],
        ];

        foreach ($marketingStages as $stageData) {
            $marketingPipeline->stages()->create($stageData);
        }
    }
}