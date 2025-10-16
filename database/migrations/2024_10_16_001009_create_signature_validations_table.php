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
        Schema::create('signature_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_request_id')->nullable();
            $table->foreign('signature_request_id')->references('id')->on('signature_requests')->onDelete('cascade');

            $table->foreignId('signature_id')->nullable();
            $table->foreign('signature_id')->references('id')->on('signatures')->onDelete('cascade');

            $table->enum('validation_type', [
                'hash_verification',
                'blockchain_verification',
                'timestamp_verification',
                'certificate_verification'
            ]);

            $table->boolean('validation_result');
            $table->json('validation_data')->nullable();

            $table->foreignId('validated_by');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('cascade');

            $table->timestamp('validated_at');
            $table->timestamps();

            // Indexes
            $table->index(['signature_request_id', 'validation_type']);
            $table->index(['signature_id', 'validation_result']);
            $table->index('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_validations');
    }
};