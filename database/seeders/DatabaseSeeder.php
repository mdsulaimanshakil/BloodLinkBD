<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Prompt 15: Seed hospitals & blood banks
        $this->call(HospitalSeeder::class);

        // Prompt 22: Seed realistic demo data (donors, requests, history)
        $this->call(DemoSeeder::class);
    }
}


