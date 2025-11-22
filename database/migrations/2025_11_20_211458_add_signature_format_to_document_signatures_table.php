<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add signature_format column to track signature format type:
     * - 'legacy_hash_only': Legacy hash-only signature (backward compatibility)
     * - 'pkcs7_cms_detached': PKCS#7/CMS detached signature (Adobe Reader compatible)
     */
    public function up(): void
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            $table->string('signature_format', 50)
                ->default('legacy_hash_only')
                ->after('cms_signature')
                ->comment('Signature format type: legacy_hash_only or pkcs7_cms_detached');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            $table->dropColumn('signature_format');
        });
    }
};
