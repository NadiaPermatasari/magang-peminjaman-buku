<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backs safe, concurrent-proof human-readable loan codes (PJ-YYYYMMDD-000001,
     * spec §13) — one row per day, incremented under a row lock inside the
     * same transaction that creates the loan (spec §14 concurrency guidance).
     */
    public function up(): void
    {
        Schema::create('loan_number_sequences', function (Blueprint $table) {
            $table->date('date')->primary();
            $table->unsignedInteger('last_number')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_number_sequences');
    }
};
