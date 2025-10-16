<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\Account;
use App\Models\Stage;
use App\Models\User;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::first();
        $salesPipeline = $account->pipelines()->where('name', 'Pipeline de Vente Principal')->first();
        $marketingPipeline = $account->pipelines()->where('name', 'Pipeline Marketing')->first();

        $stages = $salesPipeline->stages()->get();
        $marketingStages = $marketingPipeline->stages()->get();

        $users = $account->users()->get();

        // Leads pour le pipeline de vente
        $salesLeads = [
            [
                'name' => 'Jean-Claude Mba',
                'email' => 'jc.mba@entreprise-gabon.com',
                'phone' => '+241 01 11 22 33',
                'company' => 'Entreprise Gabon SARL',
                'status' => 'Nouveau',
                'source' => 'Site Web',
                'location' => 'Libreville',
                'score' => 75,
                'estimated_value' => 50000,
                'notes' => 'Prospect intéressé par nos services de gestion de leads',
                'stage_id' => $stages->where('name', 'Nouveau')->first()->id,
            ],
            [
                'name' => 'Marie Nguema',
                'email' => 'marie.nguema@oil-company.ga',
                'phone' => '+241 01 22 33 44',
                'company' => 'Oil Company Gabon',
                'status' => 'Contacté',
                'source' => 'Recommandation',
                'location' => 'Port-Gentil',
                'score' => 85,
                'estimated_value' => 75000,
                'notes' => 'Contact établi, très intéressée par notre solution',
                'stage_id' => $stages->where('name', 'Contacté')->first()->id,
            ],
            [
                'name' => 'Pierre Obame',
                'email' => 'p.obame@mining-gabon.com',
                'phone' => '+241 01 33 44 55',
                'company' => 'Mining Gabon',
                'status' => 'Qualification',
                'source' => 'Salon professionnel',
                'location' => 'Franceville',
                'score' => 90,
                'estimated_value' => 100000,
                'notes' => 'Qualifié, budget confirmé, décision prévue dans 2 semaines',
                'stage_id' => $stages->where('name', 'Qualification')->first()->id,
            ],
            [
                'name' => 'Fatou Diallo',
                'email' => 'fatou.diallo@bank-gabon.ga',
                'phone' => '+241 01 44 55 66',
                'company' => 'Bank Gabon',
                'status' => 'Négociation',
                'source' => 'Email marketing',
                'location' => 'Libreville',
                'score' => 95,
                'estimated_value' => 150000,
                'notes' => 'En négociation finale, contrat en préparation',
                'stage_id' => $stages->where('name', 'Négociation')->first()->id,
            ],
            [
                'name' => 'Alain Moussavou',
                'email' => 'a.moussavou@telecom-gabon.com',
                'phone' => '+241 01 55 66 77',
                'company' => 'Telecom Gabon',
                'status' => 'Gagné',
                'source' => 'Site Web',
                'location' => 'Libreville',
                'score' => 100,
                'estimated_value' => 200000,
                'notes' => 'Contrat signé, projet en cours de déploiement',
                'stage_id' => $stages->where('name', 'Gagné')->first()->id,
            ],
        ];

        foreach ($salesLeads as $leadData) {
            $stageId = $leadData['stage_id'];
            unset($leadData['stage_id']);

            $lead = Lead::create([
                'account_id' => $account->id,
                'current_stage_id' => $stageId,
                ...$leadData
            ]);

            // Assigner le lead à un utilisateur aléatoire
            $randomUser = $users->random();
            $lead->assignedUsers()->attach($randomUser->id, [
                'assigned_at' => now(),
                'assigned_by_user_id' => $users->first()->id,
                'notes' => 'Assigné automatiquement'
            ]);
        }

        // Leads pour le pipeline marketing
        $marketingLeads = [
            [
                'name' => 'Sarah Bongo',
                'email' => 'sarah.bongo@startup-gabon.com',
                'phone' => '+241 01 66 77 88',
                'company' => 'Startup Gabon',
                'status' => 'Chaud',
                'source' => 'Réseaux sociaux',
                'location' => 'Libreville',
                'score' => 80,
                'estimated_value' => 25000,
                'notes' => 'Lead chaud du marketing, très engagé',
                'stage_id' => $marketingStages->where('name', 'Lead Marketing')->first()->id,
            ],
            [
                'name' => 'David Mba',
                'email' => 'david.mba@consulting-gabon.com',
                'phone' => '+241 01 77 88 99',
                'company' => 'Consulting Gabon',
                'status' => 'Froid',
                'source' => 'Google Ads',
                'location' => 'Port-Gentil',
                'score' => 30,
                'estimated_value' => 15000,
                'notes' => 'Lead froid, nécessite du nurturing',
                'stage_id' => $marketingStages->where('name', 'Lead Marketing')->first()->id,
            ],
        ];

        foreach ($marketingLeads as $leadData) {
            $stageId = $leadData['stage_id'];
            unset($leadData['stage_id']);

            $lead = Lead::create([
                'account_id' => $account->id,
                'current_stage_id' => $stageId,
                ...$leadData
            ]);

            // Assigner le lead à un utilisateur marketing
            $marketingUser = $users->where('role', 'Marketing')->first();
            if ($marketingUser) {
                $lead->assignedUsers()->attach($marketingUser->id, [
                    'assigned_at' => now(),
                    'assigned_by_user_id' => $users->first()->id,
                    'notes' => 'Assigné au marketing'
                ]);
            }
        }
    }
}