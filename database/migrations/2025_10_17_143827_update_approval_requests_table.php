<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            // Update status enum untuk menambahkan status baru
            $table->dropColumn('status'); // Drop existing status column
        });

        // Add new status column with updated enum
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending',           // Menunggu approve admin/prodi
                'approved',          // Sudah diapprove, siap untuk ditandatangani user
                'user_signed',       // User sudah tanda tangan, menunggu approve sign
                'sign_approved',     // Tanda tangan sudah diapprove, dokumen final
                'invalid_sign',    // Tanda tangan dibatalkan/invalidate
                'rejected',          // Ditolak
            ])->default('pending')->after('notes');

            // Tambahkan kolom baru untuk digital signature workflow
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at');
            $table->timestamp('user_signed_at')->nullable()->after('rejected_by');
            $table->timestamp('sign_approved_at')->nullable()->after('user_signed_at');
            $table->foreignId('sign_approved_by')->nullable()->after('sign_approved_at');
            $table->text('approval_notes')->nullable()->after('sign_approved_by');
            $table->text('rejection_reason')->nullable()->after('approval_notes');

            // Kolom tambahan untuk workflow
            $table->string('document_type')->nullable()->after('rejection_reason'); // Jenis dokumen
            $table->json('workflow_metadata')->nullable()->after('document_type'); // Metadata workflow

            // Foreign key constraints
            $table->foreign('approved_by')->references('id')->on('kaprodis')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('kaprodis')->onDelete('set null');
            $table->foreign('sign_approved_by')->references('id')->on('kaprodis')->onDelete('set null');

            // Indexes untuk performa
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['approved_by', 'approved_at']);
            $table->index(['rejected_by', 'rejected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['sign_approved_by']);

            // Drop indexes
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['approved_by', 'approved_at']);

            // Drop new columns
            $table->dropColumn([
                'approved_at',
                'approved_by',
                'user_signed_at',
                'sign_approved_at',
                'sign_approved_by',
                'approval_notes',
                'rejection_reason',
                'document_type',
                'workflow_metadata',
            ]);

            // Restore original status enum
            $table->dropColumn('status');
        });

        Schema::table('approval_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('notes');
        });
    }
};
