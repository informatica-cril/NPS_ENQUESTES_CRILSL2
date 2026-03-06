<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Physiotherapist;
use App\Models\Patient;
use App\Models\SurveyResponse;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CrilNpsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ca_ES');

        // 1. Crear Admin
        Admin::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => md5('CRIL2025!'),
            ]
        );

        // 2. Crear Fisioterapeutas
        $territories = ['Zona Nord', 'Zona Centre', 'Zona Sud'];
        $physiotherapists = [];

        for ($i = 1; $i <= 3; $i++) {
            $physio = Physiotherapist::firstOrCreate(
                ['email' => "fisio{$i}@cril.cat"],
                [
                    'full_name' => "Fisioterapeuta {$i}",
                    'phone' => $faker->phoneNumber,
                    'password' => md5('fisio2025'),
                    'territory' => $territories[$i - 1],
                    'is_active' => true,
                ]
            );
            $physiotherapists[] = $physio;
        }

        // 3. Crear Pacientes
        for ($i = 1; $i <= 10; $i++) {
            $physio = $faker->randomElement($physiotherapists);
            
            Patient::firstOrCreate(
                [
                    'cip' => 'TEST' . str_pad($i, 10, '0', STR_PAD_LEFT),
                ],
                [
                    'full_name' => "Pacient {$i}",
                    'email' => "pacient{$i}@example.com",
                    'dni' => str_pad($i, 8, '0', STR_PAD_LEFT) . 'A',
                    'territory' => $physio->territory,
                    'treatment_end_date' => $faker->dateTimeBetween('now', '+6 months'),
                    'survey_completed' => $faker->boolean(30),
                    'physiotherapist_id' => $physio->id,
                ]
            );
        }

        // 4. Crear algumas respostes de enquesta
        $patients = Patient::where('survey_completed', true)->get();

        foreach ($patients as $patient) {
            SurveyResponse::create([
                'patient_id' => $patient->id,
                'nps_score' => $faker->numberBetween(0, 10),
                'service_quality' => $faker->numberBetween(1, 10),
                'punctuality' => $faker->numberBetween(1, 10),
                'treatment' => $faker->numberBetween(1, 10),
                'perceived_improvement' => $faker->numberBetween(1, 10),
                'communication' => $faker->numberBetween(1, 10),
                'global_experience' => $faker->numberBetween(1, 10),
                'duration_adequate' => $faker->boolean,
                'session_over_30_min' => $faker->boolean,
                'comments' => $faker->optional(0.6)->sentence,
            ]);
        }

        $this->command->info('✅ CRIL NPS Seeder completado!');
        $this->command->line('');
        $this->command->info('📊 Datos creados:');
        $this->command->line('  • 1 Admin (admin / CRIL2025!)');
        $this->command->line('  • 3 Fisioterapeutas (fisio1@cril.cat, fisio2@cril.cat, fisio3@cril.cat)');
        $this->command->line('  • 10 Pacientes (30% con encuesta completada)');
        $this->command->line('  • Respuestas NPS según pacientes completados');
    }
}
