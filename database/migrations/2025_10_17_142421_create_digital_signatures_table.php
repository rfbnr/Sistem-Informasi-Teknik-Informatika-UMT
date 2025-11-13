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
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('signature_id')->unique(); // ID unik untuk signature
            $table->foreignId('document_signature_id')->unique(); // NEW: 1-to-1 relationship
            $table->foreignId('user_id'); // Pemilik signature
            $table->text('public_key'); // RSA public key (2048 bit)
            $table->text('private_key'); // RSA private key (2048 bit) - encrypted
            $table->string('algorithm')->default('RSA-SHA256'); // Algoritma signature
            $table->integer('key_length')->default(2048); // Panjang kunci
            $table->text('certificate')->nullable(); // Digital certificate
            $table->timestamp('valid_from'); // Waktu mulai berlaku
            $table->timestamp('valid_until'); // Waktu berakhir
            $table->enum('status', ['active', 'expired'])->default('active');
            // $table->text('revocation_reason')->nullable(); // Alasan pencabutan
            // $table->timestamp('revoked_at')->nullable(); // Waktu pencabutan
            // $table->foreignId('created_by'); // Admin yang membuat signature
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            // $table->foreign('created_by')->references('id')->on('kaprodis')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('document_signature_id')->references('id')->on('document_signatures')->onDelete('cascade');
            $table->index(['status', 'valid_from', 'valid_until']);
            $table->index('signature_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
