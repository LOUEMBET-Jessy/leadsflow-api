<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $salesRole = Role::where('name', 'sales')->first();
        $marketingRole = Role::where('name', 'marketing')->first();

        // Get teams
        $commercialTeam = Team::where('name', 'Équipe Commerciale')->first();
        $marketingTeam = Team::where('name', 'Équipe Marketing')->first();
        $managementTeam = Team::where('name', 'Équipe Management')->first();

        $users = [
            [
                'name' => 'Admin LeadFlow',
                'email' => 'admin@leadflow.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'team_id' => $managementTeam->id,
                'current_team_id' => $managementTeam->id,
                'settings' => [
                    'notifications' => [
                        'email_notifications' => true,
                        'push_notifications' => true,
                        'lead_assigned' => true,
                        'lead_status_changed' => true,
                        'task_due' => true,
                        'task_overdue' => true,
                        'new_message' => true,
                        'weekly_summary' => true,
                        'monthly_report' => true,
                    ]
                ]
            ],
            [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@leadflow.com',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'team_id' => $commercialTeam->id,
                'current_team_id' => $commercialTeam->id,
                'settings' => [
                    'notifications' => [
                        'email_notifications' => true,
                        'push_notifications' => true,
                        'lead_assigned' => true,
                        'lead_status_changed' => true,
                        'task_due' => true,
                        'task_overdue' => true,
                        'new_message' => true,
                        'weekly_summary' => true,
                        'monthly_report' => true,
                    ]
                ]
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@leadflow.com',
                'password' => Hash::make('password'),
                'role_id' => $salesRole->id,
                'team_id' => $commercialTeam->id,
                'current_team_id' => $commercialTeam->id,
                'settings' => [
                    'notifications' => [
                        'email_notifications' => true,
                        'push_notifications' => true,
                        'lead_assigned' => true,
                        'lead_status_changed' => true,
                        'task_due' => true,
                        'task_overdue' => true,
                        'new_message' => true,
                        'weekly_summary' => true,
                        'monthly_report' => false,
                    ]
                ]
            ],
            [
                'name' => 'Pierre Durand',
                'email' => 'pierre.durand@leadflow.com',
                'password' => Hash::make('password'),
                'role_id' => $salesRole->id,
                'team_id' => $commercialTeam->id,
                'current_team_id' => $commercialTeam->id,
                'settings' => [
                    'notifications' => [
                        'email_notifications' => true,
                        'push_notifications' => false,
                        'lead_assigned' => true,
                        'lead_status_changed' => true,
                        'task_due' => true,
                        'task_overdue' => true,
                        'new_message' => true,
                        'weekly_summary' => true,
                        'monthly_report' => false,
                    ]
                ]
            ],
            [
                'name' => 'Sophie Bernard',
                'email' => 'sophie.bernard@leadflow.com',
                'password' => Hash::make('password'),
                'role_id' => $marketingRole->id,
                'team_id' => $marketingTeam->id,
                'current_team_id' => $marketingTeam->id,
                'settings' => [
                    'notifications' => [
                        'email_notifications' => true,
                        'push_notifications' => true,
                        'lead_assigned' => false,
                        'lead_status_changed' => false,
                        'task_due' => true,
                        'task_overdue' => true,
                        'new_message' => true,
                        'weekly_summary' => true,
                        'monthly_report' => true,
                    ]
                ]
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
