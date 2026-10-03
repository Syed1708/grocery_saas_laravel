<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete(); // Null for Walk-in Cash customer
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Cashier
            $table->string('invoice_no', 100)->unique();   // e.g. INV-260901-001
            $table->date('sale_date');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('vat_percent', 5, 2)->default(0.00);
            $table->decimal('vat_amount', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2);          // (Subtotal - Discount) + VAT
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);     // বাকি
            $table->decimal('change_amount', 12, 2)->default(0.00);  // ফেরত টাকা
            $table->string('payment_method', 50)->default('cash');   // cash, bkash, nagad, card, mixed, due
            $table->enum('payment_status', ['paid', 'partial', 'due'])->default('paid');
            $table->string('offline_uuid')->nullable()->unique();    // IndexedDB PWA offline sync UUID
            $table->boolean('is_synced')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};