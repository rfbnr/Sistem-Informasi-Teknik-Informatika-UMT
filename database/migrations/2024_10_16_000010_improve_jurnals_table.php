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
        Schema::table('jurnals', function (Blueprint $table) {
            // Rename name to title for clarity
            $table->renameColumn('name', 'title');

            // Add comprehensive publication fields
            $table->json('authors')->nullable()->after('title');
            $table->text('abstract')->nullable()->after('authors');
            $table->string('journal_name')->nullable()->after('category');
            $table->date('publication_date')->nullable()->after('journal_name');
            $table->string('volume')->nullable()->after('publication_date');
            $table->string('issue')->nullable()->after('volume');
            $table->string('pages')->nullable()->after('issue');
            $table->string('doi')->nullable()->after('pages');
            $table->string('url', 500)->nullable()->after('doi');
            $table->json('keywords')->nullable()->after('url');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'published', 'rejected'])->default('draft')->after('keywords');
            $table->decimal('impact_factor', 5, 2)->nullable()->after('status');

            // Add indexes
            $table->index(['publication_date', 'status']);
            $table->index(['category', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            $table->dropIndex(['publication_date', 'status']);
            $table->dropIndex(['category', 'status']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'authors', 'abstract', 'journal_name', 'publication_date',
                'volume', 'issue', 'pages', 'doi', 'url', 'keywords',
                'status', 'impact_factor'
            ]);
            $table->renameColumn('title', 'name');
        });
    }
};