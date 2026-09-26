<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks which reminder milestones have already been sent per loan so
     * the scheduled reminder command stays idempotent (spec §19/§20) —
     * re-running it never sends the same milestone twice.
     */
    public function up(): void
    {
        Schema::create('loan_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // PICKUP, DUE, OVERDUE
            $table->integer('milestone'); // days remaining / late days / pickup hours
            $table->timestamp('sent_at');

            $table->unique(['loan_id', 'type', 'milestone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_reminders');
    }
};
