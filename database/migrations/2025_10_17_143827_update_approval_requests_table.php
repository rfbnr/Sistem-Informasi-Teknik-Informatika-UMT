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
            // Tambahkan kolom nomor jika belum ada
            if (!Schema::hasColumn('approval_requests', 'nomor')) {
                $table->string('nomor')->nullable()->after('user_id');
            }

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
                'rejected',          // Ditolak
                'cancelled'          // Dibatalkan
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
            $table->string('priority')->default('normal')->after('document_type'); // Prioritas (low, normal, high, urgent)
            $table->json('workflow_metadata')->nullable()->after('priority'); // Metadata workflow
            $table->string('department')->nullable()->after('workflow_metadata'); // Departemen pemohon
            $table->timestamp('deadline')->nullable()->after('department'); // Deadline penyelesaian
            // $table->integer('revision_count')->default(0)->after('deadline'); // Jumlah revisi
            $table->text('admin_notes')->nullable()->after('deadline'); // Catatan admin

            // Foreign key constraints
            $table->foreign('approved_by')->references('id')->on('kaprodis')->onDelete('set null');
            $table->foreign('sign_approved_by')->references('id')->on('kaprodis')->onDelete('set null');

            // Indexes untuk performa
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['approved_by', 'approved_at']);
            $table->index(['rejected_by', 'rejected_at']);
            $table->index(['priority', 'deadline']);
            $table->index('nomor');
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
            $table->dropIndex(['priority', 'deadline']);
            $table->dropIndex(['nomor']);

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
                'priority',
                'workflow_metadata',
                'department',
                'deadline',
                'revision_count',
                'admin_notes'
            ]);

            // Restore original status enum
            $table->dropColumn('status');
        });

        Schema::table('approval_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('notes');
        });
    }
};
