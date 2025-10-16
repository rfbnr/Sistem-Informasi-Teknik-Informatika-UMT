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
        Schema::table('layanans', function (Blueprint $table) {
            // Rename and modify existing columns
            $table->renameColumn('keterangan', 'description');

            // Add new fields after renaming
            $table->string('category')->nullable()->after('name');
            $table->string('contact_person')->nullable()->after('description');
            $table->string('contact_email')->nullable()->after('contact_person');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->json('requirements')->nullable()->after('contact_phone');
            $table->string('process_time')->nullable()->after('requirements');

            // Modify status to be more specific
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->change();

            // Add indexes
            $table->index(['category', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layanans', function (Blueprint $table) {
            $table->dropIndex(['category', 'status']);
            $table->dropIndex(['status']);
            $table->dropColumn(['category', 'contact_person', 'contact_email', 'contact_phone', 'requirements', 'process_time']);
            $table->renameColumn('description', 'keterangan');
        });
    }
};