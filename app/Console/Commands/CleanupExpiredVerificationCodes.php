<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\VerificationCodeMapping;

/**
 * Cleanup Expired Verification Codes Command
 *
 * This command removes expired verification code mappings from the database
 * to maintain optimal performance and clean up old data.
 *
 * Usage:
 * - php artisan verification:cleanup
 * - php artisan verification:cleanup --days=365
 * - php artisan verification:cleanup --dry-run
 */
class CleanupExpiredVerificationCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verification:cleanup
                            {--days=365 : Delete mappings older than X days}
                            {--dry-run : Preview what would be deleted without actually deleting}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired verification code mappings from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('   Verification Code Mapping Cleanup');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $cutoffDate = now()->subDays($days);

        $this->info("Cleanup criteria:");
        $this->info("  • Cutoff date: {$cutoffDate->toDateTimeString()}");
        $this->info("  • Delete mappings older than: {$days} days");
        $this->newLine();

        // Get expired mappings count
        $expiredQuery = VerificationCodeMapping::where('expires_at', '<', $cutoffDate);
        $expiredCount = $expiredQuery->count();

        if ($expiredCount === 0) {
            $this->info('✓ No expired verification code mappings found.');
            $this->info('  Database is clean!');
            return 0;
        }

        // Show what will be deleted
        $this->warn("Found {$expiredCount} expired verification code mapping(s)");
        $this->newLine();

        // Get sample of items to be deleted
        $sampleMappings = $expiredQuery->limit(5)->get();

        $this->table(
            ['ID', 'Short Code', 'Document ID', 'Expired At', 'Access Count'],
            $sampleMappings->map(function ($mapping) {
                return [
                    $mapping->id,
                    $mapping->short_code,
                    $mapping->document_signature_id,
                    $mapping->expires_at->format('Y-m-d H:i:s'),
                    $mapping->access_count
                ];
            })
        );

        if ($expiredCount > 5) {
            $this->info("... and " . ($expiredCount - 5) . " more");
        }
        $this->newLine();

        // Dry run mode
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE: No data will be deleted');
            $this->info("   {$expiredCount} mapping(s) would be deleted");
            return 0;
        }

        // Confirmation prompt
        if (!$force) {
            if (!$this->confirm("Delete {$expiredCount} expired mapping(s)?", false)) {
                $this->warn('Cleanup cancelled by user');
                return 1;
            }
        }

        // Perform deletion
        $this->info('Deleting expired mappings...');

        $progressBar = $this->output->createProgressBar($expiredCount);
        $progressBar->start();

        $deletedCount = 0;
        $errorCount = 0;

        try {
            // Delete in chunks to avoid memory issues
            $expiredQuery->chunk(100, function ($mappings) use (&$deletedCount, &$errorCount, $progressBar) {
                foreach ($mappings as $mapping) {
                    try {
                        $mapping->delete();
                        $deletedCount++;
                        $progressBar->advance();
                    } catch (\Exception $e) {
                        $errorCount++;
                        Log::error('Failed to delete verification mapping', [
                            'mapping_id' => $mapping->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            $progressBar->finish();
            $this->newLine(2);

            // Summary
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('   Cleanup Summary');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info("✓ Successfully deleted: {$deletedCount} mapping(s)");

            if ($errorCount > 0) {
                $this->warn("✗ Failed to delete: {$errorCount} mapping(s)");
            }

            $this->newLine();

            // Get remaining stats
            $remainingCount = VerificationCodeMapping::count();
            $activeCount = VerificationCodeMapping::active()->count();

            $this->info('Database statistics:');
            $this->info("  • Total mappings: {$remainingCount}");
            $this->info("  • Active mappings: {$activeCount}");
            $this->info("  • Expired mappings: " . ($remainingCount - $activeCount));

            // Log cleanup
            Log::info('Verification code mapping cleanup completed', [
                'deleted_count' => $deletedCount,
                'error_count' => $errorCount,
                'cutoff_date' => $cutoffDate->toDateTimeString(),
                'days' => $days
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            Log::error('Verification code cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
