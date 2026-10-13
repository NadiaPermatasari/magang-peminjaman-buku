<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Arsip kini mencatat berkas hak atas tanah: ISBN menjadi Nomor Hak,
 * ditambah Nomor SU dan Nama Pemilik. Kategori dipakai sebagai Jenis Hak
 * dan diisi dengan daftar baku.
 */
return new class extends Migration
{
    private array $jenisHak = ['Hak Milik', 'Hak Guna Usaha', 'Hak Pakai', 'Hak Pengelolaan', 'Lainnya'];

    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['isbn']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->renameColumn('isbn', 'no_hak');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->string('no_hak', 50)->nullable()->change();
            $table->string('no_su', 50)->nullable()->after('no_hak');
            $table->string('nama_pemilik')->nullable()->after('no_su');
            $table->index('no_hak');
            $table->index('no_su');
        });

        foreach ($this->jenisHak as $name) {
            $slug = Str::slug($name);
            if (! DB::table('categories')->where('slug', $slug)->exists()) {
                DB::table('categories')->insert(['uuid' => (string) Str::uuid(), 'name' => $name, 'slug' => $slug, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['no_hak']);
            $table->dropIndex(['no_su']);
            $table->dropColumn(['no_su', 'nama_pemilik']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->renameColumn('no_hak', 'isbn');
        });
    }
};
