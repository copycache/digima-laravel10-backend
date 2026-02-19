<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Globals\Seed;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
        ]);

        // Run initial seed for MLM features, settings, and other required data
        Seed::initial_seed();
    }
}
