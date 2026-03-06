<?php

namespace Database\Seeders;

use App\Models\Centre;
use App\Models\User;
use App\Models\Fisioterapeuta;
use App\Models\Pacient;
use App\Models\Enquesta;
use App\Models\Pregunta;
use App\Models\Participacio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PruebaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create one centre
        $centre = Centre::firstOrCreate(
            ['nom' => 'CRIL TEST'],
            [
                'adreca' => 'Carrer Test 123',
                'telefon' => '933333333',
                'email' => 'cril@test.com',
                'actiu' => true,
            ]
        );

        // 2. Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'password' => md5('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 3. Create 2 fisioterapeutas
        for ($i = 1; $i <= 2; $i++) {
            $fisioUser = User::firstOrCreate(
                ['email' => "fisio$i@test.com"],
                [
                    'name' => "Fisioterapeuta $i",
                    'password' => md5('fisio123'),
                    'role' => 'fisioterapeuta',
                    'email_verified_at' => now(),
                ]
            );

            Fisioterapeuta::firstOrCreate(
                ['user_id' => $fisioUser->id],
                [
                    'centre_id' => $centre->id,
                    'nom' => "Fisioterapeuta $i",
                    'cognoms' => "Test Tester",
                    'email' => "fisio$i@test.com",
                    'data_alta' => now(),
                ]
            );
        }

        // 4. Create 5 pacients
        for ($i = 1; $i <= 5; $i++) {
            $pacientUser = User::firstOrCreate(
                ['email' => "pacient$i@test.com"],
                [
                    'name' => "Pacient $i",
                    'password' => md5('pacient123'),
                    'role' => 'pacient',
                    'email_verified_at' => now(),
                ]
            );

            Pacient::firstOrCreate(
                ['user_id' => $pacientUser->id],
                [
                    'centre_id' => $centre->id,
                    'nom' => "Pacient",
                    'cognoms' => "Test $i",
                    'cip' => 'TEST' . str_pad($i, 10, '0', STR_PAD_LEFT),
                    'dni' => str_pad($i, 8, '0', STR_PAD_LEFT) . 'A',
                    'email' => "pacient$i@test.com",
                    'data_naixement' => now()->subYears(40),
                    'data_alta' => now(),
                    'consentiment_rgpd' => true,
                ]
            );
        }

        // 5. Create an NPS survey
        $enquesta = Enquesta::create([
            'titol' => 'Enquesta NPS Test',
            'slug' => 'enquesta-nps-test-' . Str::random(6),
            'descripcio' => 'Encuesta de prueba para ver el funcionamiento',
            'tipus' => 'nps',
            'estat' => 'activa',
            'created_by' => $adminUser->id,
            'centre_id' => $centre->id,
            'data_inici' => now(),
            'anonima' => false,
            'temps_estimat_minuts' => 2,
        ]);

        // 6. Add questions to survey
        Pregunta::create([
            'enquesta_id' => $enquesta->id,
            'text_pregunta' => 'En una escala del 0 al 10, quina probabilitat hi ha que recomanis el nostre servei?',
            'tipus' => 'nps',
            'ordre' => 0,
            'obligatoria' => true,
        ]);

        Pregunta::create([
            'enquesta_id' => $enquesta->id,
            'text_pregunta' => 'Comentari (opcional)',
            'tipus' => 'text_llarg',
            'ordre' => 1,
            'obligatoria' => false,
        ]);

        // 7. Assign survey to all pacients as pending
        $pacients = Pacient::all();
        foreach ($pacients as $pacient) {
            Participacio::create([
                'enquesta_id' => $enquesta->id,
                'pacient_id' => $pacient->id,
                'estat' => 'pendent',
                'data_inici' => now(),
            ]);
        }

        $this->command->info('✅ Seeder de prueba completado!');
        $this->command->line('');
        $this->command->info('📊 Datos creados:');
        $this->command->line("  • Centro: {$centre->nom}");
        $this->command->line('  • 1 Admin (admin@test.com / admin123)');
        $this->command->line('  • 2 Fisioterapeutas (fisio1@test.com, fisio2@test.com)');
        $this->command->line('  • 5 Pacientes asignados a encuesta pendiente');
        $this->command->line('');
        $this->command->info('🔐 Credenciales de prueba:');
        $this->command->line('  Admin: admin@test.com / admin123');
        $this->command->line('  Fisio: fisio1@test.com / fisio123');
        $this->command->line('  Pacient: CATA0000000001 / pacient123 (pero usar CIP + DNI)');
    }
}