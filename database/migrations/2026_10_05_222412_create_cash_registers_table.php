<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Cashier / Admin
            $table->date('closing_date');
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->decimal('opening_cash', 14, 2)->default(0.00);
            $table->decimal('cash_sales', 14, 2)->default(0.00);
            $table->decimal('cash_due_collected', 14, 2)->default(0.00);
            $table->decimal('cash_other_income', 14, 2)->default(0.00);
            $table->decimal('cash_expenses', 14, 2)->default(0.00);
            $table->decimal('cash_supplier_paid', 14, 2)->default(0.00);
            $table->decimal('cash_refunds', 14, 2)->default(0.00);
            $table->decimal('expected_cash', 14, 2)->default(0.00);
            $table->decimal('counted_cash', 14, 2)->default(0.00);
            $table->decimal('difference', 14, 2)->default(0.00); // counted - expected (Shortage if negative, surplus if positive)
            $table->integer('total_invoices')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('closed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};