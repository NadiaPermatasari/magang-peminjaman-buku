<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rak, edisi, bahasa dan jumlah halaman tidak lagi dicatat pada arsip.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rack_id');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['edition', 'language', 'page_count']);
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('edition')->nullable()->after('publication_year');
            $table->string('language', 40)->nullable()->after('edition');
            $table->unsignedInteger('page_count')->nullable()->after('language');
            $table->foreignId('rack_id')->nullable()->after('cover_path')->constrained()->nullOnDelete();
        });
    }
};
