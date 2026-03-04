<?php

namespace Database\Seeders;

use App\Models\Fisioterapeuta;
use App\Models\User;
use App\Models\Centre;
use Illuminate\Database\Seeder;

class FisioterapeutaSeeder extends Seeder
{
    public function run(): void
    {
        $fisioterapeutes = [
            [
                'email' => 'maria.garcia@cril.es',
                'nom' => 'Maria',
                'cognoms' => 'Garcia López',
                'num_colegiat' => 'CAT-1234',
                'especialitat' => 'Traumatologia',
                'centre_code' => 'CRIL-LLE-001',
            ],
            [
                'email' => 'joan.marti@cril.es',
                'nom' => 'Joan',
                'cognoms' => 'Martí Ferrer',
                'num_colegiat' => 'CAT-2345',
                'especialitat' => 'Neurologia',
                'centre_code' => 'CRIL-LLE-001',
            ],
            [
                'email' => 'anna.puig@cril.es',
                'nom' => 'Anna',
                'cognoms' => 'Puig Soler',
                'num_colegiat' => 'CAT-3456',
                'especialitat' => 'Pediàtrica',
                'centre_code' => 'CRIL-BAL-001',
            ],
            [
                'email' => 'pere.vidal@cril.es',
                'nom' => 'Pere',
                'cognoms' => 'Vidal Roca',
                'num_colegiat' => 'CAT-4567',
                'especialitat' => 'Esportiva',
                'centre_code' => 'CRIL-TAR-001',
            ],
        ];

        foreach ($fisioterapeutes as $fisio) {
            $user = User::where('email', $fisio['email'])->first();
            $centre = Centre::where('code', $fisio['centre_code'])->first();

            Fisioterapeuta::create([
                'user_id' => $user?->id,
                'centre_id' => $centre?->id,
                'nom' => $fisio['nom'],
                'cognoms' => $fisio['cognoms'],
                'num_colegiat' => $fisio['num_colegiat'],
                'especialitat' => $fisio['especialitat'],
                'data_alta' => now()->subMonths(rand(6, 36)),
            ]);
        }
    }
}
