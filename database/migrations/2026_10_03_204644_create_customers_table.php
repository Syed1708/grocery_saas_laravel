<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // কাস্টমারের নাম
            $table->string('phone', 20)->unique();          // মোবাইল নম্বর (ইউনিক আইডি)
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(10000.00); // সর্বোচ্চ বাকির সীমা (৳)
            $table->decimal('opening_due', 12, 2)->default(0.00);      // প্রারম্ভিক বকেয়া
            $table->decimal('current_due', 12, 2)->default(0.00);      // বর্তমান বকেয়া (বাকি খাতা)
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};