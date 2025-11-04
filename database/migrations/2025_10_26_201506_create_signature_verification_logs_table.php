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
        Schema::create('signature_verification_logs', function (Blueprint $table) {
            $table->id();

            // Core fields
            $table->foreignId('document_signature_id')->nullable(); // FK ke document_signatures
            $table->foreignId('approval_request_id')->nullable(); // FK ke approval_requests
            $table->foreignId('user_id')->nullable(); // NULL jika anonymous verification

            // Verification details
            $table->string('verification_method'); // 'token', 'url', 'qr', 'id'
            $table->string('verification_token_hash'); // Hash of token untuk privacy
            $table->boolean('is_valid'); // Hasil verification
            $table->string('result_status'); // 'success', 'failed', 'expired', 'invalid'

            // Request tracking
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable(); // Dari mana user verify

            // Metadata
            $table->json('metadata')->nullable(); // Error messages, additional info
            $table->timestamp('verified_at'); // Waktu verification
            $table->timestamps();

            // Indexes untuk query performance
            $table->index(['document_signature_id', 'verified_at'], 'svl_docsig_verified_idx');
            $table->index(['is_valid', 'verified_at'], 'svl_valid_verified_idx');
            $table->index(['ip_address', 'verified_at'], 'svl_ip_verified_idx');

            $table->index(['verification_method','verified_at'], 'svl_method_verified_idx');

            // Foreign keys
            $table->foreign('document_signature_id')->references('id')->on('document_signatures')->onDelete('cascade');
            $table->foreign('approval_request_id')->references('id')->on('approval_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_verification_logs');
    }
};
