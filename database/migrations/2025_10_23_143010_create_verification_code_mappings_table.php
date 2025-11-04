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
        Schema::create('verification_code_mappings', function (Blueprint $table) {
            $table->id();

            // Short code untuk URL/QR (public-facing identifier)
            $table->string('short_code', 20)->unique()->comment('Short verification code for QR and URL');

            // Full encrypted payload (maintains existing encryption)
            $table->text('encrypted_payload')->comment('Full encrypted verification data');

            // Reference ke document_signatures
            $table->foreignId('document_signature_id')
                ->constrained('document_signatures')
                ->onDelete('cascade')
                ->comment('Reference to document signature');

            // Expiration & Security
            $table->timestamp('expires_at')->comment('When this mapping expires');

            // Audit & Analytics
            $table->unsignedInteger('access_count')->default(0)->comment('Number of times this code was accessed');
            $table->timestamp('last_accessed_at')->nullable()->comment('Last verification attempt timestamp');
            $table->string('last_accessed_ip', 45)->nullable()->comment('IP address of last access');
            $table->string('last_accessed_user_agent', 500)->nullable()->comment('User agent of last access');

            // Timestamps
            $table->timestamps();

            // Indexes for performance
            $table->index('short_code', 'idx_short_code');
            $table->index('expires_at', 'idx_expires_at');
            $table->index(['document_signature_id', 'expires_at'], 'idx_doc_sig_expires');
            $table->index('access_count', 'idx_access_count');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_code_mappings');
    }
};
