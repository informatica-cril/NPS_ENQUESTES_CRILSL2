<?php

namespace Database\Seeders;

use App\Models\Pacient;
use App\Models\Centre;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PacientSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ca_ES');
        $centres = Centre::all();

        for ($i = 0; $i < 12; $i++) {
            $sexe = $faker->randomElement(['home', 'dona']);
            
            Pacient::create([
                'centre_id' => $centres->random()->id,
                'nom' => $sexe === 'home' ? $faker->firstNameMale : $faker->firstNameFemale,
                'cognoms' => $faker->lastName . ' ' . $faker->lastName,
                'dni' => $faker->numerify('########') . $faker->randomLetter(),
                'cip' => 'CATA' . $faker->numerify('############'),
                'data_naixement' => $faker->dateTimeBetween('-80 years', '-18 years'),
                'sexe' => $sexe,
                'telefon' => $faker->phoneNumber,
                'email' => $faker->optional(0.7)->safeEmail,
                'adreca' => $faker->streetAddress,
                'poblacio' => $faker->city,
                'codi_postal' => $faker->postcode,
                'data_alta' => $faker->dateTimeBetween('-2 years', 'now'),
                'consentiment_rgpd' => $faker->boolean(85),
                'data_consentiment' => $faker->optional(0.85)->dateTimeThisYear,
            ]);
        }
    }
}
