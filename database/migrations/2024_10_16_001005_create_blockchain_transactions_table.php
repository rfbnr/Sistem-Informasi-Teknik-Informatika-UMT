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
        Schema::create('blockchain_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_request_id')->nullable();
            $table->foreign('signature_request_id')->references('id')->on('signature_requests')->onDelete('set null');

            $table->foreignId('signature_id')->nullable();
            $table->foreign('signature_id')->references('id')->on('signatures')->onDelete('set null');

            $table->string('transaction_hash', 66)->unique(); // Ethereum tx hash
            $table->bigInteger('block_number')->nullable();

            $table->enum('transaction_type', [
                'document_hash_store',
                'signature_store',
                'signature_verification',
                'access_grant',
                'access_revoke'
            ]);

            $table->string('contract_address', 42)->nullable(); // Ethereum contract address
            $table->bigInteger('gas_used')->nullable();
            $table->decimal('gas_price', 20, 0)->nullable(); // Wei

            $table->enum('status', [
                'pending',
                'confirmed',
                'failed',
                'reverted'
            ])->default('pending');

            $table->json('metadata')->nullable();
            $table->foreignId('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();

            // Indexes with custom names
            $table->index(['signature_request_id', 'transaction_type'], 'bt_sig_req_type_idx');
            $table->index(['signature_id', 'status'], 'bt_sig_status_idx');
            $table->index(['transaction_hash', 'status'], 'bt_hash_status_idx');
            $table->index('block_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_transactions');
    }
};
