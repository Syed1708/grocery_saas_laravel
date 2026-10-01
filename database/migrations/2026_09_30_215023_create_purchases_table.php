<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('chalan_no', 100)->unique();      // চালান / মেমো নম্বর
            $table->date('purchase_date');
            $table->decimal('subtotal', 12, 2);              // পণ্যের মূল দাম
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('transport_cost', 12, 2)->default(0.00); // কুলি / গাড়ি ভাড়া
            $table->decimal('grand_total', 12, 2);           // (Subtotal - Discount) + Transport
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);   // মহাজন বাকি
            $table->string('payment_method', 50)->default('cash'); // cash, bank, bkash, nagad
            $table->enum('payment_status', ['paid', 'partial', 'due'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};