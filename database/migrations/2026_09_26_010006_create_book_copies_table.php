<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->string('barcode')->unique();
            $table->string('inventory_code')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('source')->nullable();
            $table->string('condition', 30)->default('GOOD');
            $table->string('status', 20)->default('AVAILABLE')->index();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
