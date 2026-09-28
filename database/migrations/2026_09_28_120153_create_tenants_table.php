<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // e.g. মদিনা জেনারেল স্টোর
            $table->string('slug')->unique();            // madina-store
            $table->string('owner_name');
            $table->string('phone', 20)->unique();       // 017xxxxxxxx
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('trade_license')->nullable(); // BIN / Trade License
            $table->string('logo')->nullable();
            $table->string('currency', 10)->default('BDT');
            $table->string('currency_symbol', 10)->default('৳');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('settings')->nullable();        // Store invoice header, footer, thermal width
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};