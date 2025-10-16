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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');

            $table->integer('version_number')->default(1);
            $table->string('file_path');
            $table->string('file_hash', 64);
            $table->text('changes_description')->nullable();

            $table->foreignId('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'version_number']);
            $table->unique(['document_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};