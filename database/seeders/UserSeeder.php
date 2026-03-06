<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user - login with username "admin" and password "CRIL2025"
        User::create([
            'name' => 'Administrador CRIL',
            'username' => 'admin',
            'email' => 'admin@cril.es',
            'password' => md5('CRIL2025'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Viewer user
        User::create([
            'name' => 'Usuari Visor',
            'email' => 'viewer@cril.es',
            'password' => md5('password123'),
            'role' => 'viewer',
            'email_verified_at' => now(),
        ]);

        // Physiotherapist users (will be linked in FisioterapeutaSeeder)
        $fisioUsers = [
            ['name' => 'Maria Garcia', 'email' => 'maria.garcia@cril.es'],
            ['name' => 'Joan Martí', 'email' => 'joan.marti@cril.es'],
            ['name' => 'Anna Puig', 'email' => 'anna.puig@cril.es'],
            ['name' => 'Pere Vidal', 'email' => 'pere.vidal@cril.es'],
        ];

        foreach ($fisioUsers as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => md5('password123'),
                'role' => 'fisioterapeuta',
                'email_verified_at' => now(),
            ]);
        }
    }
}
