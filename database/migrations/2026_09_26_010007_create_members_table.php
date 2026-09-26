<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * phone/address/identity_number are stored encrypted (spec §11, see the
     * `encrypted` casts on the Member model). identity_number_index is a
     * keyed-HMAC blind index (App\Support\BlindIndex) so uniqueness/lookup
     * still works without storing the NIK in a directly searchable form.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('member_number')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->text('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('identity_number')->nullable();
            $table->string('identity_number_index', 64)->nullable()->unique();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->date('joined_at');
            $table->date('expired_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
