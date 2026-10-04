<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->string('month', 20);                    // e.g. September
            $table->integer('year');                        // e.g. 2026
            $table->date('salary_date');                    // বিতরণের তারিখ
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('allowance', 12, 2)->default(0.00);       // খাবার / মোবাইল বিল
            $table->decimal('overtime_amount', 12, 2)->default(0.00); // ওভারটাইম
            $table->decimal('advance_deduction', 12, 2)->default(0.00); // 🚀 অগ্রিম কর্তন (Auto deducted!)
            $table->decimal('penalty_deduction', 12, 2)->default(0.00); // অনুপস্থিতি / জরিমানা
            $table->decimal('net_salary', 12, 2);           // (Basic + Allowance + Overtime) - (Advance + Penalty)
            $table->string('payment_method', 50)->default('cash'); // cash, bank, bkash
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('paid');
            $table->string('payslip_no', 50)->unique();      // e.g. PAY-2609-001
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'month', 'year']);  // Prevent duplicate salary for same month
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};