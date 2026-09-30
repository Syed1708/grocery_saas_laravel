<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->enum('type', ['capital', 'drawing']);   // capital = Investment (+), drawing = Personal Withdrawal (-)
            $table->date('transaction_date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50)->default('cash'); // cash, bank, bkash, nagad
            $table->string('reference')->nullable();        // Bank cheque no, bKash TrxID
            $table->string('purpose')->nullable();          // e.g. দোকানের নতুন মাল ক্রয়, পরিবারের খরচ
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_transactions');
    }
};