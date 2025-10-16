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
        Schema::table('lombas', function (Blueprint $table) {
            // Drop old columns and add new structure
            $table->dropColumn(['month', 'bidang', 'tempat']);

            // Add new comprehensive fields
            $table->text('description')->nullable()->after('name');
            $table->string('category')->nullable()->after('description');
            $table->enum('level', ['local', 'regional', 'national', 'international'])->default('local')->after('category');
            $table->string('organizer')->nullable()->after('level');
            $table->string('location')->nullable()->after('organizer');
            $table->date('start_date')->nullable()->after('location');
            $table->date('end_date')->nullable()->after('start_date');
            $table->datetime('registration_deadline')->nullable()->after('end_date');
            $table->text('prize')->nullable()->after('registration_deadline');
            $table->json('participants')->nullable()->after('prize');
            $table->json('achievements')->nullable()->after('participants');
            $table->string('image')->nullable()->after('achievements');
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming')->after('image');

            // Add indexes
            $table->index(['start_date', 'status']);
            $table->index(['category', 'level']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lombas', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'status']);
            $table->dropIndex(['category', 'level']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'description', 'category', 'level', 'organizer', 'location',
                'start_date', 'end_date', 'registration_deadline', 'prize',
                'participants', 'achievements', 'image', 'status'
            ]);

            // Restore old columns
            $table->string('month')->nullable();
            $table->string('bidang')->nullable();
            $table->string('tempat')->nullable();
        });
    }
};