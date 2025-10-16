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
        Schema::create('document_hashes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');

            $table->enum('hash_type', ['sha256', 'sha512', 'md5'])->default('sha256');
            $table->string('hash_value', 128);
            $table->string('blockchain_tx_hash')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'hash_type']);
            $table->index('hash_value');
            $table->index('blockchain_tx_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_hashes');
    }
};