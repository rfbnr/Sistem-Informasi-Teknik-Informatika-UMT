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
            $table->foreignId('digital_signature_id'); // FK ke digital_signatures
            $table->string('document_hash'); // Hash dokumen original
            $table->text('signature_value')->nullable(); // Nilai signature digital
            $table->json('signature_metadata')->nullable(); // Metadata signature (JSON)
            $table->string('qr_code_path')->nullable(); // Path QR code untuk verifikasi
            $table->text('verification_url')->nullable(); // URL untuk verifikasi
            $table->text('cms_signature')->nullable(); // CMS signature format
            $table->timestamp('signed_at')->nullable(); // Waktu penandatanganan
            $table->foreignId('signed_by')->nullable(); // Kaprodi yang menandatangani
            $table->enum('signature_status', ['pending', 'signed', 'verified', 'invalid', 'rejected'])->default('pending');
            $table->string('canvas_data_path')->nullable(); // Path canvas data untuk signature
            $table->json('positioning_data')->nullable(); // Data posisi barcode dan signature
            $table->string('final_pdf_path')->nullable(); // Path PDF final yang sudah ditandatangani
            $table->text('verification_token')->nullable(); // Token untuk verifikasi publik
            $table->timestamp('verified_at')->nullable(); // Waktu verifikasi
            $table->foreignId('verified_by')->nullable(); // Yang melakukan verifikasi
            $table->timestamps();

            $table->foreign('approval_request_id')->references('id')->on('approval_requests')->onDelete('cascade');
            $table->foreign('digital_signature_id')->references('id')->on('digital_signatures')->onDelete('cascade');
            $table->foreign('signed_by')->references('id')->on('kaprodis')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('kaprodis')->onDelete('set null');

            $table->index(['document_hash', 'signature_status']);
            // $table->index('verification_url');
            // $table->index('verification_token');
            $table->index(['signed_at', 'signature_status']);
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
