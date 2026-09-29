<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key')->unique();
            $table->string('client_name');                   // e.g. মেসার্স মদিনা জেনারেল স্টোর
            $table->string('client_phone');
            $table->decimal('monthly_fee', 10, 2)->default(1500.00); // 1,500 BDT
            $table->date('expires_at');                      // Expiry Date
            $table->integer('grace_period_days')->default(2); // Extra days before hard lock
            $table->string('bkash_number')->default('01700000000');
            $table->string('nagad_number')->default('01800000000');
            $table->string('support_phone')->default('01900000000');
            $table->string('support_whatsapp')->default('8801900000000');
            $table->enum('status', ['active', 'expired', 'suspended'])->default('active');
            $table->string('last_trx_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};