<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Kaprodi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create Admin User
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@umt.ac.id',
            'password' => Hash::make('password'),
            'roles' => 'admin',
        ]);

        // Create Regular User (Mahasiswa)
        User::factory()->create([
            'name' => 'Ridwan Febnur Asri Redinda',
            'email' => 'ridwanfebnur9@gmail.com',
            'password' => Hash::make('password'),
            'roles' => 'user',
            'NIM' => '2024001001',
        ]);

        Kaprodi::create([
            'name' => 'Dr. Budi Santoso, M.Kom',
            'email' => 'ridwanfebnur88@gmail.com',
            'password' => 'password', // akan di-hash otomatis oleh mutator
            'NIDN' => '0123456789',
        ]);

        // Create initial digital signature for the system
        // $this->call(DigitalSignatureSeeder::class);

        // Create signature template for kaprodi
        // $this->call(SignatureTemplateSeeder::class);
    }
}
