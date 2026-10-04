<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('income_source');                // e.g. খালি বস্তা বিক্রি, কার্টুন বিক্রি, বিকাশ কমিশন
            $table->string('receipt_no', 100)->unique();     // e.g. INC-260901-001
            $table->date('income_date');
            $table->decimal('amount', 12, 2);               // আয়ের পরিমাণ (৳)
            $table->string('payment_method', 50)->default('cash'); // cash, bkash, bank
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_incomes');
    }
};