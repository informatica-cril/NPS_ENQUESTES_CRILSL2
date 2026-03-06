<?php

namespace Database\Seeders;

use App\Models\Enquesta;
use App\Models\Pregunta;
use App\Models\Participacio;
use App\Models\Pacient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnquestaMensualSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->error('No admin user found!');
            return;
        }

        // Create monthly survey
        $enquesta = Enquesta::create([
            'titol' => 'Enquesta de Seguiment Mensual - Març 2026',
            'slug' => 'seguiment-mensual-març-2026-' . Str::random(6),
            'descripcio' => 'Enquesta mensual per avaluar la teva satisfacció amb el servei de fisioteràpia.',
            'tipus' => 'nps',
            'estat' => 'activa',
            'created_by' => $admin->id,
            'data_inici' => now(),
            'anonima' => false,
            'temps_estimat_minuts' => 2,
        ]);

        // NPS Question
        Pregunta::create([
            'enquesta_id' => $enquesta->id,
            'text_pregunta' => 'En una escala del 0 al 10, quina probabilitat hi ha que recomanis el nostre servei a un amic o familiar?',
            'tipus' => 'nps',
            'ordre' => 0,
            'obligatoria' => true,
        ]);

        // Get all patients
        $pacients = Pacient::all();

        if ($pacients->isEmpty()) {
            $this->command->warn('No patients found to assign the survey to.');
            return;
        }

        // Create pending participations for all patients
        foreach ($pacients as $pacient) {
            Participacio::create([
                'enquesta_id' => $enquesta->id,
                'pacient_id' => $pacient->id,
                'estat' => 'pendent',
                'data_inici' => now(),
            ]);
        }

        $this->command->info("Monthly survey created and assigned to {$pacients->count()} patients.");
    }
}