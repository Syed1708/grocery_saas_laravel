<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->enum('type', ['received', 'issued']); // received = কাস্টমার থেকে প্রাপ্ত, issued = মহাজনকে প্রদত্ত
            $table->enum('party_type', ['customer', 'supplier', 'other'])->default('other');
            $table->string('party_name')->nullable();
            $table->string('bank_name');
            $table->string('cheque_number', 100);
            $table->date('cheque_date');
            $table->date('clearing_date')->nullable();
            $table->decimal('amount', 14, 2);
            $table->enum('status', ['pending', 'cleared', 'bounced', 'cancelled'])->default('pending');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete(); // Account where cleared
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_cheques');
    }
};