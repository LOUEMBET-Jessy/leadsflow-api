<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AccountSeeder::class,
            UserSeeder::class,
            PipelineSeeder::class,
            LeadSeeder::class,
            InteractionSeeder::class,
            TaskSeeder::class,
        ]);
    }
}