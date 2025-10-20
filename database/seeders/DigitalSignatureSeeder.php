<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Services\DigitalSignatureService;
use Illuminate\Support\Facades\Log;

class DigitalSignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $signatureService = new DigitalSignatureService();

        try {
            // Get admin user
            $admin = User::where('roles', 'admin')->first();

            if (!$admin) {
                $this->command->warn('No admin user found. Please run DatabaseSeeder first.');
                return;
            }

            // Create initial digital signature for the system
            $signature = $signatureService->createDigitalSignature(
                'Document Signing - System Default',
                $admin->id,
                5 // Valid for 5 years
            );

            $this->command->info("✓ Digital Signature created successfully!");
            $this->command->info("  - Signature ID: {$signature->signature_id}");
            $this->command->info("  - Valid Until: {$signature->valid_until->format('Y-m-d H:i:s')}");
            $this->command->info("  - Key Length: {$signature->key_length} bits");
            $this->command->info("  - Algorithm: {$signature->algorithm}");

        } catch (\Exception $e) {
            $this->command->error('Failed to create digital signature: ' . $e->getMessage());
            Log::error('DigitalSignatureSeeder failed: ' . $e->getMessage());
        }
    }
}
