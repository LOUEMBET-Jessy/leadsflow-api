<?php

namespace Database\Seeders;

use App\Models\Interaction;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InteractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = Lead::all();
        $users = User::all();

        $interactionTypes = ['email_sent', 'email_received', 'call', 'meeting', 'note', 'status_change'];

        $interactionTemplates = [
            'email_sent' => [
                'summary' => 'Email envoyé',
                'details' => 'Email de suivi envoyé au client avec les informations demandées.',
            ],
            'email_received' => [
                'summary' => 'Email reçu',
                'details' => 'Réponse du client à notre proposition.',
            ],
            'call' => [
                'summary' => 'Appel téléphonique',
                'details' => 'Appel avec le client pour discuter de ses besoins.',
            ],
            'meeting' => [
                'summary' => 'Réunion',
                'details' => 'Réunion en présentiel ou visioconférence avec le client.',
            ],
            'note' => [
                'summary' => 'Note interne',
                'details' => 'Note ajoutée par l\'équipe commerciale.',
            ],
            'status_change' => [
                'summary' => 'Changement de statut',
                'details' => 'Le statut du lead a été modifié.',
            ],
        ];

        // Create interactions for each lead
        foreach ($leads as $lead) {
            $interactionCount = rand(2, 8);
            $assignedUser = $lead->assignedTo ?? $users->random();

            for ($i = 0; $i < $interactionCount; $i++) {
                $type = fake()->randomElement($interactionTypes);
                $template = $interactionTemplates[$type];
                $interactionDate = Carbon::now()->subDays(rand(0, 60));

                Interaction::create([
                    'lead_id' => $lead->id,
                    'user_id' => $assignedUser->id,
                    'type' => $type,
                    'summary' => $template['summary'],
                    'details' => $template['details'],
                    'interaction_date' => $interactionDate,
                    'attachments' => fake()->boolean(20) ? ['document.pdf', 'image.jpg'] : null,
                    'created_at' => $interactionDate,
                    'updated_at' => $interactionDate,
                ]);
            }
        }

        // Create additional random interactions
        for ($i = 0; $i < 30; $i++) {
            $lead = $leads->random();
            $user = $users->random();
            $type = fake()->randomElement($interactionTypes);
            $template = $interactionTemplates[$type];
            $interactionDate = Carbon::now()->subDays(rand(0, 90));

            Interaction::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'type' => $type,
                'summary' => $template['summary'],
                'details' => $template['details'],
                'interaction_date' => $interactionDate,
                'attachments' => fake()->boolean(15) ? ['document.pdf'] : null,
                'created_at' => $interactionDate,
                'updated_at' => $interactionDate,
            ]);
        }
    }
}
