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
        Schema::create('signature_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_signature_id')->nullable(); // FK ke document_signatures
            $table->foreignId('approval_request_id')->nullable(); // FK ke approval_requests
            $table->foreignId('user_id'); // User yang melakukan aksi
            $table->string('action'); // Aksi yang dilakukan (create, sign, verify, approve, etc)
            $table->string('status_from')->nullable(); // Status sebelumnya
            $table->string('status_to')->nullable(); // Status sesudahnya
            $table->text('description'); // Deskripsi aksi
            $table->json('metadata')->nullable(); // Data tambahan
            $table->string('ip_address')->nullable(); // IP address
            $table->string('user_agent')->nullable(); // User agent
            $table->timestamp('performed_at'); // Waktu aksi dilakukan
            $table->timestamps();

            $table->foreign('document_signature_id')->references('id')->on('document_signatures')->onDelete('cascade');
            $table->foreign('approval_request_id')->references('id')->on('approval_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['user_id', 'performed_at']);
            $table->index(['action', 'performed_at']);
            $table->index(['document_signature_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_audit_logs');
    }
};
