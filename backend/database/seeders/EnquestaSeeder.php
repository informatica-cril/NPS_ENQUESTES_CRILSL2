<?php

namespace Database\Seeders;

use App\Models\Enquesta;
use App\Models\Pregunta;
use App\Models\Participacio;
use App\Models\Resposta;
use App\Models\NpsResultat;
use App\Models\Pacient;
use App\Models\Fisioterapeuta;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class EnquestaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ca_ES');
        $admin = User::where('role', 'admin')->first();

        // Create NPS Survey
        $npsEnquesta = Enquesta::create([
            'titol' => 'Enquesta de Satisfacció NPS - CRIL',
            'slug' => 'enquesta-nps-cril-' . Str::random(6),
            'descripcio' => 'Enquesta per mesurar la satisfacció dels pacients amb els serveis de fisioterapía del CRIL.',
            'tipus' => 'nps',
            'estat' => 'activa',
            'created_by' => $admin->id,
            'data_inici' => now()->subMonths(6),
            'anonima' => false,
            'temps_estimat_minuts' => 3,
        ]);

        // NPS Question
        $npsQ = Pregunta::create([
            'enquesta_id' => $npsEnquesta->id,
            'text_pregunta' => 'En una escala del 0 al 10, quina probabilitat hi ha que recomanis el nostre servei a un amic o familiar?',
            'tipus' => 'nps',
            'ordre' => 0,
            'obligatoria' => true,
        ]);

        Pregunta::create([
            'enquesta_id' => $npsEnquesta->id,
            'text_pregunta' => 'Què ha influit més en la teva valoració?',
            'tipus' => 'opcio_unica',
            'ordre' => 1,
            'obligatoria' => false,
            'opcions' => [
                'Atenció del fisioterapeuta',
                'Resultats del tractament',
                'Instal·lacions',
                'Temps d\'espera',
                'Preu',
                'Altres',
            ],
        ]);

        Pregunta::create([
            'enquesta_id' => $npsEnquesta->id,
            'text_pregunta' => 'Tens algun comentari o suggeriment per millorar el nostre servei?',
            'tipus' => 'text_llarg',
            'ordre' => 2,
            'obligatoria' => false,
        ]);

        // Create sample participations and responses
        $pacients = Pacient::all();
        $fisioterapeutes = Fisioterapeuta::all();

        $comentarisPositius = [
            'Excel·lent atenció, molt professional!',
            'Estic molt satisfet amb el tractament',
            'El fisioterapeuta és molt atent i explicà',
            'Grans professionals, molt recomanable',
            'He millorat molt gràcies al seu tractament',
        ];

        $comentarisNeutres = [
            'El servei està bé, però podria millorar els temps d\'espera',
            'Correcte en general',
            'L\'atenció és bona però les instal·lacions necessiten renovació',
        ];

        $comentarisNegatius = [
            'Temps d\'espera massa llargs',
            'No he notat millora',
            'Massa car per al servei ofert',
        ];

        // Generate 100 sample participations over last 6 months
        for ($i = 0; $i < 100; $i++) {
            $pacient = $pacients->random();
            $fisio = $fisioterapeutes->random();
            $date = $faker->dateTimeBetween('-6 months', 'now');

            // Weighted NPS score (more positive)
            $weights = [0 => 2, 1 => 2, 2 => 3, 3 => 3, 4 => 4, 5 => 5, 6 => 8, 7 => 15, 8 => 20, 9 => 25, 10 => 13];
            $puntuacio = $this->weightedRandom($weights);

            $participacio = Participacio::create([
                'enquesta_id' => $npsEnquesta->id,
                'pacient_id' => $pacient->id,
                'fisioterapeuta_id' => $fisio->id,
                'estat' => 'completada',
                'data_inici' => $date,
                'data_completat' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // NPS Response
            Resposta::create([
                'participacio_id' => $participacio->id,
                'pregunta_id' => $npsQ->id,
                'valor_numeric' => $puntuacio,
            ]);

            // Select comment based on score
            $comentari = null;
            if ($faker->boolean(40)) {
                if ($puntuacio >= 9) {
                    $comentari = $faker->randomElement($comentarisPositius);
                } elseif ($puntuacio >= 7) {
                    $comentari = $faker->randomElement($comentarisNeutres);
                } else {
                    $comentari = $faker->randomElement($comentarisNegatius);
                }
            }

            // NPS Result
            NpsResultat::create([
                'enquesta_id' => $npsEnquesta->id,
                'participacio_id' => $participacio->id,
                'centre_id' => $fisio->centre_id,
                'fisioterapeuta_id' => $fisio->id,
                'puntuacio' => $puntuacio,
                'comentari' => $comentari,
                'data' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // Create satisfaction survey
        $satEnquesta = Enquesta::create([
            'titol' => 'Enquesta de Qualitat del Servei',
            'slug' => 'enquesta-qualitat-' . Str::random(6),
            'descripcio' => 'Enquesta detallada sobre la qualitat dels nostres serveis.',
            'tipus' => 'satisfaccio',
            'estat' => 'activa',
            'created_by' => $admin->id,
            'data_inici' => now()->subMonths(3),
            'anonima' => true,
            'temps_estimat_minuts' => 5,
        ]);

        $satPreguntes = [
            ['text' => 'Com valores l\'atenció rebuda pel personal?', 'tipus' => 'escala'],
            ['text' => 'Les instal·lacions són adequades?', 'tipus' => 'si_no'],
            ['text' => 'El temps d\'espera ha estat acceptable?', 'tipus' => 'escala'],
            ['text' => 'Has aconseguit els objectius del tractament?', 'tipus' => 'opcio_unica', 'opcions' => ['Sí, completament', 'Parcialment', 'No', 'Encara estic en tractament']],
            ['text' => 'Observacions addicionals', 'tipus' => 'text_llarg'],
        ];

        foreach ($satPreguntes as $i => $p) {
            Pregunta::create([
                'enquesta_id' => $satEnquesta->id,
                'text_pregunta' => $p['text'],
                'tipus' => $p['tipus'],
                'ordre' => $i,
                'obligatoria' => $i < 3,
                'opcions' => $p['opcions'] ?? null,
                'configuracio' => $p['tipus'] === 'escala' ? ['min' => 1, 'max' => 5] : null,
            ]);
        }
    }

    private function weightedRandom(array $weights): int
    {
        $sum = array_sum($weights);
        $rand = mt_rand(1, $sum);
        $cumulative = 0;

        foreach ($weights as $value => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $value;
            }
        }

        return array_key_last($weights);
    }
}
