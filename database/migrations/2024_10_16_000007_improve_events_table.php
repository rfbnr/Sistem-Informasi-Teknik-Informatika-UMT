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
        Schema::table('events', function (Blueprint $table) {
            // Change description to text type
            $table->text('description')->nullable()->change();

            // Add new fields
            $table->string('location')->nullable()->after('end_time');
            $table->string('organizer')->nullable()->after('location');
            $table->integer('max_participants')->nullable()->after('organizer');
            $table->integer('current_participants')->default(0)->after('max_participants');
            $table->datetime('registration_deadline')->nullable()->after('current_participants');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('active')->after('registration_deadline');
            $table->enum('event_type', ['seminar', 'workshop', 'competition', 'conference', 'other'])->default('other')->after('status');
            $table->string('image')->nullable()->after('event_type');

            // Add indexes
            $table->index(['start_time', 'status']);
            $table->index('event_type');
            $table->index('registration_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['start_time', 'status']);
            $table->dropIndex(['event_type']);
            $table->dropIndex(['registration_deadline']);
            $table->dropColumn([
                'location', 'organizer', 'max_participants', 'current_participants',
                'registration_deadline', 'status', 'event_type', 'image'
            ]);
        });
    }
};