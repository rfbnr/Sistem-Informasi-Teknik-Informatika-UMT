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
        Schema::create('signature_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama template (e.g., "Template Kaprodi TI")
            $table->text('description')->nullable(); // Deskripsi template
            $table->string('signature_image_path'); // Path gambar tanda tangan kaprodi
            $table->string('logo_path')->nullable(); // Path logo institusi
            $table->json('layout_config'); // Konfigurasi layout (posisi barcode, ttd, dll)
            $table->foreignId('kaprodi_id'); // FK ke users (kaprodi)
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false); // Apakah template default
            $table->string('canvas_width')->default('800'); // Lebar canvas
            $table->string('canvas_height')->default('600'); // Tinggi canvas
            $table->string('background_color')->default('#ffffff'); // Warna background
            $table->integer('usage_count')->default(0); // Jumlah penggunaan template
            $table->timestamp('last_used_at')->nullable(); // Terakhir digunakan
            $table->timestamps();

            $table->foreign('kaprodi_id')->references('id')->on('kaprodis')->onDelete('cascade');
            $table->index(['status', 'is_default']);
            $table->index(['kaprodi_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_templates');
    }
};
