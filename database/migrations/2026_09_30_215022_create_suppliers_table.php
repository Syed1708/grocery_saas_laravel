<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // মহাজন / এজেন্সির নাম (e.g. মেসার্স হাজী এন্টারপ্রাইজ)
            $table->string('company_name')->nullable();     // Distributor Brand (e.g. প্রাণ ডিলার)
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();   // SR / Delivery Rep Name
            $table->decimal('opening_due', 12, 2)->default(0.00); // প্রারম্ভিক বকেয়া
            $table->decimal('current_due', 12, 2)->default(0.00); // বর্তমান মহাজন বাকি
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};