<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Modul Penulis & Penerbit dihapus dari aplikasi: data buku kini hanya
 * memakai kategori dan rak. Migrasi pembuat tabelnya sudah tidak ada lagi,
 * jadi untuk instalasi baru seluruh blok di up() tidak melakukan apa-apa —
 * ini khusus merapikan database yang sudah pernah jalan dengan modul itu.
 * Struktur tabel bisa dipulihkan lewat down(), tetapi isinya tidak.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('books', 'publisher_id')) {
            Schema::table('books', function (Blueprint $table) {
                // SQLite (dipakai di test) tidak mendukung DROP FOREIGN KEY;
                // kolomnya saja yang dibuang dan FK ikut hilang bersamanya.
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['publisher_id']);
                }

                $table->dropColumn('publisher_id');
            });
        }

        Schema::dropIfExists('author_book');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('publishers');
    }

    public function down(): void
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('author_book', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->primary(['book_id', 'author_id']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('publisher_id')->nullable()->after('category_id')->constrained()->restrictOnDelete();
        });
    }
};
