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
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_request_id');
            $table->foreign('signature_request_id')->references('id')->on('signature_requests')->onDelete('cascade');

            $table->foreignId('signer_id');
            $table->foreign('signer_id')->references('id')->on('users')->onDelete('cascade');

            $table->longText('signature_data')->nullable(); // Base64 encoded signature
            $table->string('signature_hash', 64); // SHA-256 hash of signature
            $table->datetime('signed_at')->nullable();

            $table->enum('signature_method', [
                'digital_signature',
                'electronic_signature',
                'biometric_signature',
                'pin_signature',
                'otp_signature'
            ])->default('digital_signature');

            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('location')->nullable(); // GPS coordinates, timezone, etc.

            $table->enum('status', [
                'pending',
                'signed',
                'rejected',
                'expired'
            ])->default('pending');

            $table->string('blockchain_tx_hash')->nullable();
            $table->string('verification_code', 20)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['signature_request_id', 'signer_id']);
            $table->index(['signer_id', 'status']);
            $table->index('verification_code');
            $table->index('blockchain_tx_hash');
            $table->index('signature_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};