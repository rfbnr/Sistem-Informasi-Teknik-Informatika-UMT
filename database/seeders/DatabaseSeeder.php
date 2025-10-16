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
            'phone' => '081234567000',
            'address' => 'Jl. Perintis Kemerdekaan I No.33, Tangerang',
            'status' => 'active',
        ]);

        // Create Regular User (Mahasiswa)
        User::factory()->create([
            'name' => 'Muhammad Rizki',
            'email' => 'user@umt.ac.id',
            'password' => Hash::make('password'),
            'roles' => 'user',
            'NIM' => '2024001001',
            'phone' => '081234567001',
            'address' => 'Jl. Merdeka No.10, Tangerang',
            'semester' => 5,
            'angkatan' => 2022,
            'status' => 'active',
        ]);

        // Create Additional User (Mahasiswa)
        User::factory()->create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@umt.ac.id',
            'password' => Hash::make('password'),
            'roles' => 'user',
            'NIM' => '2024001002',
            'phone' => '081234567002',
            'address' => 'Jl. Sudirman No.25, Tangerang',
            'semester' => 3,
            'angkatan' => 2023,
            'status' => 'active',
        ]);

        // Create Kaprodi 1 - Informatika
        Kaprodi::create([
            'name' => 'Dr. Budi Santoso, M.Kom',
            'email' => 'kaprodi.informatika@umt.ac.id',
            'password' => 'password', // akan di-hash otomatis oleh mutator
            'NIDN' => '0123456789',
            'phone' => '081234567890',
            'jabatan' => 'Kepala Program Studi Informatika',
            'status' => 'active',
        ]);

        // Create Kaprodi 2 - Teknik Sipil
        Kaprodi::create([
            'name' => 'Dr. Siti Aminah, M.T',
            'email' => 'kaprodi.sipil@umt.ac.id',
            'password' => 'password', // akan di-hash otomatis oleh mutator
            'NIDN' => '0987654321',
            'phone' => '081234567891',
            'jabatan' => 'Kepala Program Studi Teknik Sipil',
            'status' => 'active',
        ]);
    }
}
