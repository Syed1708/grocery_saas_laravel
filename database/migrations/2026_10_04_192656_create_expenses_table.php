<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Spender / Cashier
            $table->string('voucher_no', 100)->unique();     // e.g. EXP-260901-001
            $table->date('expense_date');
            $table->decimal('amount', 12, 2);               // খরচের পরিমাণ (৳)
            $table->string('payment_method', 50)->default('cash'); // cash, bank, bkash, nagad
            $table->string('reference')->nullable();         // Bill no, TrxID, Cheque no
            $table->text('note')->nullable();                // খরচের বিবরণ
            $table->string('receipt_image')->nullable();     // Voucher attachment
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};