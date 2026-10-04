<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->enum('type', [
                'deposit', 'withdraw', 'transfer_in', 'transfer_out',
                'sale', 'purchase', 'expense', 'income', 'payroll',
                'loan_disbursement', 'loan_repayment', 'adjustment'
            ]);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->date('transaction_date');
            $table->string('reference_type')->nullable(); // e.g. Sale, Purchase, Loan
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('voucher_no', 100)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};