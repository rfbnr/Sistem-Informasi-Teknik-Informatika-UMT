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
        Schema::table('approval_requests', function (Blueprint $table) {
            // Add missing nomor field
            $table->string('nomor', 10)->unique()->after('user_id');

            // Add approval tracking fields
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->after('approved_at');
            $table->foreign('approved_by')->references('id')->on('kaprodis')->nullOnDelete();

            // Add indexes for better performance
            $table->index(['status', 'created_at']);
            $table->index('nomor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['nomor']);
            $table->dropColumn(['nomor', 'approved_at', 'approved_by']);
        });
    }
};