<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = Lead::all();
        $users = User::all();

        $taskTemplates = [
            [
                'title' => 'Appeler le client',
                'description' => 'Prendre contact avec le client pour discuter de ses besoins',
                'priority' => 'high',
            ],
            [
                'title' => 'Envoyer la proposition',
                'description' => 'Préparer et envoyer la proposition commerciale',
                'priority' => 'high',
            ],
            [
                'title' => 'Programmer une démonstration',
                'description' => 'Organiser une démonstration de la solution',
                'priority' => 'medium',
            ],
            [
                'title' => 'Suivi de relance',
                'description' => 'Relancer le client après la première prise de contact',
                'priority' => 'medium',
            ],
            [
                'title' => 'Préparer le contrat',
                'description' => 'Rédiger le contrat commercial',
                'priority' => 'low',
            ],
            [
                'title' => 'Analyser les besoins',
                'description' => 'Analyser en détail les besoins du client',
                'priority' => 'medium',
            ],
            [
                'title' => 'Recherche concurrentielle',
                'description' => 'Analyser la concurrence et les alternatives',
                'priority' => 'low',
            ],
            [
                'title' => 'Présentation produit',
                'description' => 'Présenter les fonctionnalités du produit',
                'priority' => 'high',
            ],
        ];

        $statuses = ['todo', 'in_progress', 'completed'];

        // Create tasks for each lead
        foreach ($leads as $lead) {
            $taskCount = rand(1, 4);
            $assignedUser = $lead->assignedTo ?? $users->random();

            for ($i = 0; $i < $taskCount; $i++) {
                $template = fake()->randomElement($taskTemplates);
                $status = fake()->randomElement($statuses);
                $dueDate = Carbon::now()->addDays(rand(-7, 14));

                Task::create([
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'due_date' => $dueDate,
                    'priority' => $template['priority'],
                    'status' => $status,
                    'assigned_to_user_id' => $assignedUser->id,
                    'created_by_user_id' => $lead->created_by_user_id,
                    'lead_id' => $lead->id,
                    'completion_date' => $status === 'completed' ? $dueDate->subDays(rand(0, 3)) : null,
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 7)),
                ]);
            }
        }

        // Create additional standalone tasks
        for ($i = 0; $i < 15; $i++) {
            $template = fake()->randomElement($taskTemplates);
            $status = fake()->randomElement($statuses);
            $assignedUser = $users->random();
            $createdUser = $users->random();
            $dueDate = Carbon::now()->addDays(rand(-5, 21));

            Task::create([
                'title' => $template['title'],
                'description' => $template['description'],
                'due_date' => $dueDate,
                'priority' => $template['priority'],
                'status' => $status,
                'assigned_to_user_id' => $assignedUser->id,
                'created_by_user_id' => $createdUser->id,
                'lead_id' => null,
                'completion_date' => $status === 'completed' ? $dueDate->subDays(rand(0, 3)) : null,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 7)),
            ]);
        }
    }
}
