<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\PruebaSeeder;

class RunPruebaSeeder extends Command
{
    protected $signature = 'seed:prueba';
    protected $description = 'Run the prueba seeder for testing purposes';

    public function handle()
    {
        $this->info('🌱 Ejecutando seeder de prueba...');
        $this->call('migrate:fresh');
        $this->call('db:seed', ['--class' => 'PruebaSeeder']);
        $this->info('✅ ¡Seeder completado!');
    }
}
