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
        Schema::table('alumnis', function (Blueprint $table) {
            $table->string('NIM')->unique()->after('name');
            $table->year('tahun_lulus')->after('NIM');
            $table->string('company')->nullable()->after('tahun_lulus');
            $table->string('phone')->nullable()->after('email');
            $table->json('achievements')->nullable()->after('tiktok');
            $table->text('testimonial')->nullable()->after('achievements');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('testimonial');

            // Modify existing columns for longer URLs
            $table->string('linkedin', 500)->nullable()->change();
            $table->string('instagram', 500)->nullable()->change();
            $table->string('youtube', 500)->nullable()->change();
            $table->string('tiktok', 500)->nullable()->change();

            // Add indexes
            $table->index(['tahun_lulus', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnis', function (Blueprint $table) {
            $table->dropUnique(['NIM']);
            $table->dropIndex(['tahun_lulus', 'status']);
            $table->dropIndex(['status']);
            $table->dropColumn(['NIM', 'tahun_lulus', 'company', 'phone', 'achievements', 'testimonial', 'status']);
        });
    }
};