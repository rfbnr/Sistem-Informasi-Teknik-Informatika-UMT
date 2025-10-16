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
        Schema::table('agendas', function (Blueprint $table) {
            // Change description to text type
            $table->text('description')->nullable()->change();

            // Add new fields
            $table->datetime('start_date')->nullable()->after('description');
            $table->datetime('end_date')->nullable()->after('start_date');
            $table->string('location')->nullable()->after('end_date');
            $table->string('organizer')->nullable()->after('location');
            $table->integer('max_participants')->nullable()->after('organizer');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('active')->after('max_participants');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('status');

            // Add indexes
            $table->index(['start_date', 'status']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'status']);
            $table->dropIndex(['priority']);
            $table->dropColumn(['start_date', 'end_date', 'location', 'organizer', 'max_participants', 'status', 'priority']);
        });
    }
};