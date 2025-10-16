<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Lead;
use App\Models\User;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $leads = Lead::all();
        $users = User::all();

        $taskTitles = [
            'Appeler le prospect',
            'Envoyer la proposition commerciale',
            'Planifier une démonstration',
            'Suivre le budget',
            'Préparer la présentation',
            'Contacter le décideur',
            'Analyser les besoins',
            'Envoyer les documents',
            'Confirmer la réunion',
            'Faire le suivi post-réunion',
        ];

        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['EnCours', 'Retard', 'Complete'];

        foreach ($leads as $lead) {
            // Créer 1-3 tâches par lead
            $taskCount = rand(1, 3);
            
            for ($i = 0; $i < $taskCount; $i++) {
                $user = $users->random();
                $title = $taskTitles[array_rand($taskTitles)];
                $priority = $priorities[array_rand($priorities)];
                $status = $statuses[array_rand($statuses)];
                
                $dueDate = now()->addDays(rand(1, 14));
                $completedAt = $status === 'Complete' ? now()->subDays(rand(0, 7)) : null;

                Task::create([
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'title' => $title,
                    'description' => $this->getDescriptionForTask($title, $lead),
                    'priority' => $priority,
                    'status' => $status,
                    'due_date' => $dueDate,
                    'completed_at' => $completedAt,
                    'completion_notes' => $status === 'Complete' ? $this->getCompletionNotes($title) : null,
                    'reminders' => $this->getReminders($priority),
                ]);
            }
        }

        // Créer quelques tâches générales (sans lead associé)
        $generalTasks = [
            'Préparer la réunion d\'équipe',
            'Mettre à jour la base de données',
            'Analyser les performances du mois',
            'Former les nouveaux commerciaux',
            'Optimiser les processus de vente',
        ];

        foreach ($generalTasks as $title) {
            $user = $users->random();
            $priority = $priorities[array_rand($priorities)];
            $status = $statuses[array_rand($statuses)];
            
            $dueDate = now()->addDays(rand(1, 30));
            $completedAt = $status === 'Complete' ? now()->subDays(rand(0, 14)) : null;

            Task::create([
                'lead_id' => null,
                'user_id' => $user->id,
                'title' => $title,
                'description' => "Tâche générale : {$title}",
                'priority' => $priority,
                'status' => $status,
                'due_date' => $dueDate,
                'completed_at' => $completedAt,
                'completion_notes' => $status === 'Complete' ? "Tâche terminée avec succès" : null,
                'reminders' => $this->getReminders($priority),
            ]);
        }
    }

    private function getDescriptionForTask($title, $lead)
    {
        return match($title) {
            'Appeler le prospect' => "Appeler {$lead->name} de {$lead->company} pour faire le suivi de notre proposition",
            'Envoyer la proposition commerciale' => "Préparer et envoyer la proposition commerciale personnalisée pour {$lead->company}",
            'Planifier une démonstration' => "Organiser une démonstration de LeadFlow pour l'équipe de {$lead->company}",
            'Suivre le budget' => "Vérifier le budget disponible chez {$lead->company} et les processus d'approbation",
            'Préparer la présentation' => "Créer une présentation personnalisée pour {$lead->company} basée sur leurs besoins",
            'Contacter le décideur' => "Identifier et contacter le décideur final chez {$lead->company}",
            'Analyser les besoins' => "Analyser en détail les besoins spécifiques de {$lead->company}",
            'Envoyer les documents' => "Envoyer les documents contractuels et techniques à {$lead->company}",
            'Confirmer la réunion' => "Confirmer la réunion prévue avec {$lead->name} et son équipe",
            'Faire le suivi post-réunion' => "Faire le suivi après la réunion avec {$lead->company} et envoyer les prochaines étapes",
            default => "Tâche liée au prospect {$lead->name} de {$lead->company}"
        };
    }

    private function getCompletionNotes($title)
    {
        return match($title) {
            'Appeler le prospect' => "Appel effectué avec succès, le prospect est intéressé",
            'Envoyer la proposition commerciale' => "Proposition envoyée, en attente de retour",
            'Planifier une démonstration' => "Démonstration planifiée pour la semaine prochaine",
            'Suivre le budget' => "Budget confirmé, processus d'approbation en cours",
            'Préparer la présentation' => "Présentation prête, très bien reçue par le client",
            'Contacter le décideur' => "Décideur contacté, réunion planifiée",
            'Analyser les besoins' => "Analyse terminée, rapport envoyé au client",
            'Envoyer les documents' => "Documents envoyés, signature en cours",
            'Confirmer la réunion' => "Réunion confirmée, tous les participants présents",
            'Faire le suivi post-réunion' => "Suivi effectué, prochaines étapes définies",
            default => "Tâche terminée avec succès"
        };
    }

    private function getReminders($priority)
    {
        return match($priority) {
            'urgent' => [
                ['type' => 'email', 'time' => '1 hour before'],
                ['type' => 'sms', 'time' => '30 minutes before'],
            ],
            'high' => [
                ['type' => 'email', 'time' => '2 hours before'],
            ],
            'medium' => [
                ['type' => 'email', 'time' => '1 day before'],
            ],
            'low' => [
                ['type' => 'email', 'time' => '2 days before'],
            ],
            default => []
        };
    }
}