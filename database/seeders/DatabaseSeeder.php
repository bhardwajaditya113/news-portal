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
        // Activate all roles and permissions
        $this->call(ActivateAllRolesSeeder::class);
        
        // Seed news sources for real-time fetching
        $this->call(NewsSourceSeeder::class);
    }
}
