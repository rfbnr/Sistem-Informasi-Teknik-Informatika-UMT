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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'academic_transcript',
                'certificate',
                'thesis',
                'research_proposal',
                'internship_report',
                'other'
            ])->default('other');

            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('file_hash', 64); // SHA-256 hash
            $table->string('mime_type');

            $table->enum('status', [
                'uploaded',
                'processing',
                'ready_for_signature',
                'signed',
                'archived',
                'deleted'
            ])->default('uploaded');

            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['category', 'status']);
            $table->index('file_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};