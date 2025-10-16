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
        Schema::table('dosens', function (Blueprint $table) {
            $table->string('NIDN')->unique()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->text('bio')->nullable()->after('tiktok');
            $table->json('research_interests')->nullable()->after('bio');
            $table->enum('status', ['active', 'inactive', 'retired'])->default('active')->after('research_interests');

            // Modify existing columns
            $table->string('linkedin', 500)->nullable()->change();
            $table->string('instagram', 500)->nullable()->change();
            $table->string('youtube', 500)->nullable()->change();
            $table->string('tiktok', 500)->nullable()->change();

            // Add indexes
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            $table->dropUnique(['NIDN']);
            $table->dropIndex(['status']);
            $table->dropColumn(['NIDN', 'phone', 'bio', 'research_interests', 'status']);
        });
    }
};