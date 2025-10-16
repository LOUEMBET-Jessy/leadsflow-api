<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = LeadStatus::all();
        $stages = PipelineStage::all();
        $users = User::all();

        $sources = ['Web Form', 'Email', 'LinkedIn', 'Referral', 'Cold Call', 'Event', 'Social Media'];
        $priorities = ['Hot', 'Warm', 'Cold'];
        $industries = ['Technology', 'Finance', 'Healthcare', 'Manufacturing', 'Retail', 'Education', 'Real Estate'];
        $companySizes = ['Small', 'Medium', 'Large'];

        $leads = [
            [
                'first_name' => 'Jean',
                'last_name' => 'Dubois',
                'email' => 'jean.dubois@techcorp.com',
                'phone' => '+33123456789',
                'company' => 'TechCorp Solutions',
                'title' => 'CEO',
                'source' => 'LinkedIn',
                'priority' => 'Hot',
                'industry' => 'Technology',
                'company_size' => 'Large',
                'notes' => 'Très intéressé par notre solution. Budget confirmé pour Q1.',
                'address' => '123 Avenue des Champs-Élysées',
                'city' => 'Paris',
                'country' => 'France',
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Leroy',
                'email' => 'marie.leroy@financeplus.fr',
                'phone' => '+33987654321',
                'company' => 'Finance Plus',
                'title' => 'Directrice Marketing',
                'source' => 'Web Form',
                'priority' => 'Warm',
                'industry' => 'Finance',
                'company_size' => 'Medium',
                'notes' => 'Demande de démonstration. Intéressée par les fonctionnalités d\'automatisation.',
                'address' => '456 Rue de la Paix',
                'city' => 'Lyon',
                'country' => 'France',
            ],
            [
                'first_name' => 'Pierre',
                'last_name' => 'Moreau',
                'email' => 'pierre.moreau@healthcare.org',
                'phone' => '+33555666777',
                'company' => 'Healthcare Systems',
                'title' => 'IT Director',
                'source' => 'Email',
                'priority' => 'Warm',
                'industry' => 'Healthcare',
                'company_size' => 'Large',
                'notes' => 'Évaluation de solutions CRM. Besoin de conformité RGPD.',
                'address' => '789 Boulevard Saint-Germain',
                'city' => 'Marseille',
                'country' => 'France',
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Petit',
                'email' => 'sophie.petit@retailco.com',
                'phone' => '+33444555666',
                'company' => 'RetailCo',
                'title' => 'Sales Manager',
                'source' => 'Referral',
                'priority' => 'Cold',
                'industry' => 'Retail',
                'company_size' => 'Small',
                'notes' => 'Recommandé par un client existant. Premier contact prévu la semaine prochaine.',
                'address' => '321 Rue du Commerce',
                'city' => 'Toulouse',
                'country' => 'France',
            ],
            [
                'first_name' => 'Antoine',
                'last_name' => 'Rousseau',
                'email' => 'antoine.rousseau@manufacturing.fr',
                'phone' => '+33333444555',
                'company' => 'Manufacturing Pro',
                'title' => 'Operations Director',
                'source' => 'Cold Call',
                'priority' => 'Hot',
                'industry' => 'Manufacturing',
                'company_size' => 'Large',
                'notes' => 'Urgent besoin de digitalisation des processus. Budget important disponible.',
                'address' => '654 Avenue Industrielle',
                'city' => 'Lille',
                'country' => 'France',
            ],
        ];

        foreach ($leads as $index => $leadData) {
            $status = $statuses->random();
            $stage = $stages->random();
            $assignedUser = $users->where('role.name', '!=', 'admin')->random();
            $createdUser = $users->random();

            Lead::create([
                ...$leadData,
                'status_id' => $status->id,
                'pipeline_stage_id' => $stage->id,
                'assigned_to_user_id' => $assignedUser->id,
                'created_by_user_id' => $createdUser->id,
                'score' => rand(20, 90),
                'last_contact_date' => Carbon::now()->subDays(rand(0, 30)),
                'created_at' => Carbon::now()->subDays(rand(0, 60)),
                'updated_at' => Carbon::now()->subDays(rand(0, 7)),
            ]);
        }

        // Create additional random leads
        for ($i = 0; $i < 20; $i++) {
            $status = $statuses->random();
            $stage = $stages->random();
            $assignedUser = $users->where('role.name', '!=', 'admin')->random();
            $createdUser = $users->random();

            Lead::create([
                'first_name' => fake('fr_FR')->firstName(),
                'last_name' => fake('fr_FR')->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'company' => fake('fr_FR')->company(),
                'title' => fake('fr_FR')->jobTitle(),
                'source' => fake()->randomElement($sources),
                'priority' => fake()->randomElement($priorities),
                'industry' => fake()->randomElement($industries),
                'company_size' => fake()->randomElement($companySizes),
                'notes' => fake('fr_FR')->paragraph(),
                'address' => fake('fr_FR')->streetAddress(),
                'city' => fake('fr_FR')->city(),
                'country' => 'France',
                'status_id' => $status->id,
                'pipeline_stage_id' => $stage->id,
                'assigned_to_user_id' => $assignedUser->id,
                'created_by_user_id' => $createdUser->id,
                'score' => rand(10, 95),
                'last_contact_date' => Carbon::now()->subDays(rand(0, 30)),
                'created_at' => Carbon::now()->subDays(rand(0, 90)),
                'updated_at' => Carbon::now()->subDays(rand(0, 14)),
            ]);
        }
    }
}
