<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul Denda dihapus dari aplikasi. Keterlambatan tetap dicatat lewat
 * status OVERDUE pada peminjaman, hanya saja tidak ada perhitungan uang.
 * Migrasi pembuat kedua tabel sudah dihapus, jadi pada instalasi baru up()
 * tidak melakukan apa-apa — ini untuk database yang sudah berjalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('fine_payments');
        Schema::dropIfExists('fines');
    }

    public function down(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('loan_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->string('type', 30)->default('LATE_RETURN');
            $table->unsignedInteger('late_days')->default(0);
            $table->unsignedInteger('rate')->default(0);
            $table->unsignedInteger('amount')->default(0);
            $table->string('status', 20)->default('UNPAID')->index();
            $table->timestamp('calculated_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('waived_at')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('waive_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('fine_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('fine_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('amount');
            $table->timestamp('paid_at');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('method', 30)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }
};
