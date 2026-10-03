<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengajuan perpanjangan / banding peminjaman: anggota meminta tambahan
 * hari, petugas atau admin menyetujui (due_at digeser) atau menolak.
 * previous_due_at / new_due_at disimpan agar riwayat tetap terbaca walau
 * jatuh tempo peminjaman berubah lagi setelahnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_extensions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('days');
            $table->string('reason', 500);
            $table->string('status', 20)->default('PENDING')->index();
            $table->timestamp('requested_at');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note', 500)->nullable();
            $table->timestamp('previous_due_at')->nullable();
            $table->timestamp('new_due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_extensions');
    }
};
