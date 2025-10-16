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
        Schema::table('kaprodis', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('jabatan')->nullable()->after('NIDN');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('jabatan');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->rememberToken()->after('password');

            // Add unique constraint
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kaprodis', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['phone', 'jabatan', 'status', 'email_verified_at', 'remember_token']);
        });
    }
};
