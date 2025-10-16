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
        Schema::create('signature_request_signees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_request_id');
            $table->foreign('signature_request_id')->references('id')->on('signature_requests')->onDelete('cascade');

            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->enum('role', [
                'signer',
                'reviewer',
                'approver',
                'witness',
                'cc'
            ])->default('signer');

            $table->integer('order')->default(1); // Urutan tanda tangan untuk sequential workflow

            $table->enum('status', [
                'pending',
                'notified',
                'viewed',
                'signed',
                'rejected',
                'skipped'
            ])->default('pending');

            $table->boolean('required')->default(true); // Apakah tanda tangan wajib
            $table->datetime('notified_at')->nullable();
            $table->datetime('viewed_at')->nullable();
            $table->datetime('responded_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['signature_request_id', 'user_id']);
            $table->index(['signature_request_id', 'order']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_request_signees');
    }
};