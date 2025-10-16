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
        Schema::create('document_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');

            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->enum('access_type', ['read', 'write', 'sign', 'admin'])->default('read');

            $table->foreignId('granted_by');
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('cascade');

            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'user_id']);
            $table->index(['user_id', 'access_type']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_accesses');
    }
};