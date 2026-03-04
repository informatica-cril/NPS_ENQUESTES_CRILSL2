<?php

namespace Database\Seeders;

use App\Models\Centre;
use Illuminate\Database\Seeder;

class CentreSeeder extends Seeder
{
    public function run(): void
    {
        $centres = [
            [
                'name' => 'CRIL Lleida Central',
                'code' => 'CRIL-LLE-001',
                'address' => 'Carrer Major, 45',
                'city' => 'Lleida',
                'postal_code' => '25002',
                'province' => 'Lleida',
                'latitude' => 41.6148,
                'longitude' => 0.6262,
                'phone' => '+34 973 123 456',
                'email' => 'lleida@cril.es',
            ],
            [
                'name' => 'CRIL Balaguer',
                'code' => 'CRIL-BAL-001',
                'address' => 'Plaça Mercadal, 12',
                'city' => 'Balaguer',
                'postal_code' => '25600',
                'province' => 'Lleida',
                'latitude' => 41.7897,
                'longitude' => 0.8052,
                'phone' => '+34 973 234 567',
                'email' => 'balaguer@cril.es',
            ],
            [
                'name' => 'CRIL Tàrrega',
                'code' => 'CRIL-TAR-001',
                'address' => 'Carrer del Carme, 8',
                'city' => 'Tàrrega',
                'postal_code' => '25300',
                'province' => 'Lleida',
                'latitude' => 41.6467,
                'longitude' => 1.1395,
                'phone' => '+34 973 345 678',
                'email' => 'tarrega@cril.es',
            ],
            [
                'name' => 'CRIL Mollerussa',
                'code' => 'CRIL-MOL-001',
                'address' => 'Avinguda Catalunya, 23',
                'city' => 'Mollerussa',
                'postal_code' => '25230',
                'province' => 'Lleida',
                'latitude' => 41.6311,
                'longitude' => 0.8965,
                'phone' => '+34 973 456 789',
                'email' => 'mollerussa@cril.es',
            ],
        ];

        foreach ($centres as $centre) {
            Centre::create($centre);
        }
    }
}
