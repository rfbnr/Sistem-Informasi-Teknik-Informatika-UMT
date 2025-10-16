<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('signature_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');

            $table->foreignId('requester_id');
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('workflow_type', [
                'sequential',    // Satu per satu berurutan
                'parallel',      // Semua bisa tanda tangan bersamaan
                'conditional'    // Berdasarkan kondisi tertentu
            ])->default('sequential');

            $table->datetime('deadline')->nullable();

            $table->enum('status', [
                'draft',
                'pending',
                'in_progress',
                'completed',
                'expired',
                'cancelled',
                'rejected'
            ])->default('draft');

            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'urgent'
            ])->default('medium');

            $table->json('metadata')->nullable();
            $table->string('blockchain_tx_hash')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'status']);
            $table->index(['requester_id', 'status']);
            $table->index(['deadline', 'status']);
            $table->index('blockchain_tx_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_requests');
    }
};