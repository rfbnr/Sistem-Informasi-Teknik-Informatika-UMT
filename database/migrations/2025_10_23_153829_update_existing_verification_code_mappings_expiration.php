<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Update Existing Verification Code Mappings Expiration
 *
 * This migration updates existing verification code mappings to respect
 * their associated DigitalSignature's valid_until date.
 *
 * Changes:
 * - Updates expires_at to minimum of current expires_at or digital_signature.valid_until
 * - Logs all changes for audit trail
 * - Skips mappings where digital signature is not found
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->info('Starting verification code mappings expiration update...');

        // Get all mappings that might need updating
        $mappings = DB::table('verification_code_mappings as vcm')
            ->join('document_signatures as doc', 'vcm.document_signature_id', '=', 'doc.id')
            ->join('digital_signatures as ds', 'doc.id', '=', 'ds.document_signature_id')
            // ->join('digital_signatures as ds', 'doc.digital_signature_id', '=', 'ds.id')
            ->select(
                'vcm.id',
                'vcm.short_code',
                'vcm.expires_at as current_expires',
                'ds.valid_until as signature_expires',
                'ds.id as digital_signature_id',
                'ds.status as signature_status'
            )
            ->get();

        $totalMappings = $mappings->count();
        $updatedCount = 0;
        $skippedCount = 0;
        $errors = [];

        $this->info("Found {$totalMappings} verification code mappings to process...");

        foreach ($mappings as $mapping) {
            try {
                $currentExpires = \Carbon\Carbon::parse($mapping->current_expires);
                $signatureExpires = \Carbon\Carbon::parse($mapping->signature_expires);

                // Check if mapping expires after signature
                if ($currentExpires > $signatureExpires) {
                    // Update to signature expiry
                    $newExpires = $signatureExpires;

                    DB::table('verification_code_mappings')
                        ->where('id', $mapping->id)
                        ->update([
                            'expires_at' => $newExpires,
                            'updated_at' => now()
                        ]);

                    $updatedCount++;

                    Log::info('Updated verification code mapping expiration', [
                        'mapping_id' => $mapping->id,
                        'short_code' => $mapping->short_code,
                        'old_expires' => $currentExpires->toDateTimeString(),
                        'new_expires' => $newExpires->toDateTimeString(),
                        'reason' => 'signature_expires_earlier'
                    ]);

                    $this->info("  ✓ Updated {$mapping->short_code}: {$currentExpires->format('Y-m-d')} → {$newExpires->format('Y-m-d')}");
                } else {
                    // Already correct or signature expires later
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'mapping_id' => $mapping->id,
                    'error' => $e->getMessage()
                ];

                Log::error('Failed to update verification code mapping', [
                    'mapping_id' => $mapping->id,
                    'error' => $e->getMessage()
                ]);

                $this->error("  ✗ Failed to update mapping {$mapping->id}: {$e->getMessage()}");
            }
        }

        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  Migration Summary');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("  Total mappings processed: {$totalMappings}");
        $this->info("  Updated: {$updatedCount}");
        $this->info("  Skipped (already correct): {$skippedCount}");
        $this->info("  Errors: " . count($errors));
        $this->newLine();

        if (count($errors) > 0) {
            $this->warn('Some mappings failed to update. Check logs for details.');
        }

        Log::info('Verification code mappings expiration update completed', [
            'total' => $totalMappings,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'errors' => count($errors)
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed as we don't store the original expiration dates
        // The change is also a data fix rather than a schema change
        $this->warn('This migration cannot be reversed automatically.');
        $this->warn('Original expiration dates were not stored.');
        $this->info('If needed, restore from database backup.');
    }

    /**
     * Helper method to output info (for non-artisan contexts)
     */
    private function info($message)
    {
        if (app()->runningInConsole()) {
            echo $message . PHP_EOL;
        }
    }

    /**
     * Helper method to output errors
     */
    private function error($message)
    {
        if (app()->runningInConsole()) {
            echo "ERROR: " . $message . PHP_EOL;
        }
    }

    /**
     * Helper method to output warnings
     */
    private function warn($message)
    {
        if (app()->runningInConsole()) {
            echo "WARNING: " . $message . PHP_EOL;
        }
    }

    /**
     * Helper method for new line
     */
    private function newLine($count = 1)
    {
        if (app()->runningInConsole()) {
            echo str_repeat(PHP_EOL, $count);
        }
    }
};
