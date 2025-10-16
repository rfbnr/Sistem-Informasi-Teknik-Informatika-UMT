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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->integer('semester')->nullable()->after('NIM');
            $table->year('angkatan')->nullable()->after('semester');
            $table->enum('status', ['active', 'inactive', 'graduated', 'suspended'])->default('active')->after('angkatan');

            // Add indexes
            $table->index(['NIM', 'status']);
            $table->index('angkatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['NIM', 'status']);
            $table->dropIndex(['angkatan']);
            $table->dropColumn(['phone', 'address', 'semester', 'angkatan', 'status']);
        });
    }
};