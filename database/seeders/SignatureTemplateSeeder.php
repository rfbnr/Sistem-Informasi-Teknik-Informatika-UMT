<?php

namespace Database\Seeders;

use App\Models\Kaprodi;
use Illuminate\Database\Seeder;
use App\Models\SignatureTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SignatureTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Get kaprodi
            $kaprodi = Kaprodi::first();

            if (!$kaprodi) {
                $this->command->warn('No Kaprodi found. Please run DatabaseSeeder first.');
                return;
            }

            // Create sample signature template
            // Note: You'll need to add actual signature image manually to storage/app/signature-templates/

            // Create directory if not exists
            if (!Storage::exists('signature-templates')) {
                Storage::makeDirectory('signature-templates');
                $this->command->info('Created signature-templates directory');
            }

            // Create sample template (without actual image - needs manual upload)
            $template = SignatureTemplate::create([
                'kaprodi_id' => $kaprodi->id,
                'name' => 'Default Kaprodi Signature',
                'description' => 'Template tanda tangan default untuk Kaprodi',
                'signature_image_path' => 'signature-templates/default-signature.png',
                'canvas_width' => 400,
                'canvas_height' => 200,
                'is_default' => true,
                'is_active' => true,
                'text_config' => [
                    'show_name' => true,
                    'show_nidn' => true,
                    'show_title' => true,
                    'show_date' => true,
                    'font_size' => 12,
                    'font_family' => 'Arial',
                    'text_color' => '#000000'
                ],
                'layout_config' => [
                    'signature_position' => 'top',
                    'text_position' => 'bottom',
                    'text_align' => 'center',
                    'padding' => 10,
                    'border' => false
                ]
            ]);

            $this->command->info("✓ Signature Template created successfully!");
            $this->command->info("  - Template ID: {$template->id}");
            $this->command->info("  - Name: {$template->name}");
            $this->command->info("  - Kaprodi: {$kaprodi->name}");
            $this->command->warn("  ⚠ IMPORTANT: You need to manually upload signature image to:");
            $this->command->warn("     storage/app/signature-templates/default-signature.png");
            $this->command->warn("  Or use the admin panel to upload signature image.");

        } catch (\Exception $e) {
            $this->command->error('Failed to create signature template: ' . $e->getMessage());
            Log::error('SignatureTemplateSeeder failed: ' . $e->getMessage());
        }
    }
}
