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
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id'); // FK ke approval_requests
            // $table->foreignId('digital_signature_id'); // FK ke digital_signatures
            $table->string('document_hash')->nullable(); // Hash dokumen original
            $table->text('signature_value')->nullable(); // Nilai signature digital
            $table->json('signature_metadata')->nullable(); // Metadata signature (JSON)
            $table->string('temporary_qr_code_path')->nullable(); // Path QR code sementara sebelum finalisasi
            $table->string('qr_code_path')->nullable(); // Path QR code untuk verifikasi
            $table->text('verification_url')->nullable(); // URL untuk verifikasi
            $table->text('cms_signature')->nullable(); // CMS signature format
            $table->timestamp('signed_at')->nullable(); // Waktu penandatanganan
            $table->foreignId('signed_by')->nullable(); // Kaprodi yang menandatangani
            $table->string('invalidated_reason')->nullable(); // Alasan pembatalan signature
            $table->timestamp('invalidated_at')->nullable(); // Waktu pembatalan signature
            $table->enum('signature_status', ['pending', 'signed', 'verified', 'invalid'])->default('pending');
            $table->json('qr_positioning_data')->nullable(); // Data posisi barcode signature
            $table->string('final_pdf_path')->nullable(); // Path PDF final yang sudah ditandatangani
            $table->text('verification_token')->nullable(); // Token untuk verifikasi publik
            $table->timestamps();

            $table->foreign('approval_request_id')->references('id')->on('approval_requests')->onDelete('cascade');
            // $table->foreign('digital_signature_id')->references('id')->on('digital_signatures')->onDelete('cascade');
            $table->foreign('signed_by')->references('id')->on('kaprodis')->onDelete('cascade');

            $table->index(['document_hash', 'signature_status']);
            // $table->index('verification_url');
            // $table->index('verification_token');
            $table->index(['signed_at', 'signature_status']);
            $table->index('invalidated_at', 'invalidated_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
