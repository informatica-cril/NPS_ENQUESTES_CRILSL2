<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CentreSeeder::class,
            UserSeeder::class,
            FisioterapeutaSeeder::class,
            PacientSeeder::class,
            EnquestaSeeder::class,
        ]);
    }
}
