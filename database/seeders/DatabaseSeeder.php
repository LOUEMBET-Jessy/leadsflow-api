<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // PermissionSeeder::class, // Commenté temporairement
            RoleSeeder::class,
            TeamSeeder::class,
            LeadStatusSeeder::class,
            UserSeeder::class,
            PipelineSeeder::class,
            LeadSeeder::class,
            TaskSeeder::class,
            InteractionSeeder::class,
        ]);
    }
}