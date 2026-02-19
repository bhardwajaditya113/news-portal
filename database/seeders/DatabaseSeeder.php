<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed core platform data
        $this->call([
            AdminSeeder::class,
            LanguageSeeder::class,
            FooterInfoSeeder::class,
            AdSeeder::class,
            NewsSourceSeeder::class,
        ]);

        // Activate all roles and permissions (keep at end)
        $this->call(ActivateAllRolesSeeder::class);
    }
}
