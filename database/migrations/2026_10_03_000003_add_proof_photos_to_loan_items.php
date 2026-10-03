<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bukti foto serah terima (saat buku diserahkan ke anggota) dan bukti foto
 * pengembalian. Keduanya disimpan di disk `public` (storage/app/public),
 * kolom ini hanya menyimpan path relatifnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_items', function (Blueprint $table) {
            $table->string('handover_photo_path')->nullable()->after('condition_on_borrow');
            $table->string('return_photo_path')->nullable()->after('condition_on_return');
        });
    }

    public function down(): void
    {
        Schema::table('loan_items', function (Blueprint $table) {
            $table->dropColumn(['handover_photo_path', 'return_photo_path']);
        });
    }
};
