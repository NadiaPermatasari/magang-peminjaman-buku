<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
