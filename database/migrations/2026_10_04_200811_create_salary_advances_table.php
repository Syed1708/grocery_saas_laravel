<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->date('advance_date');
            $table->decimal('amount', 12, 2);               // অগ্রিম উত্তোলিত টাকা (৳)
            $table->string('payment_method', 50)->default('cash'); // cash, bkash
            $table->string('purpose')->nullable();          // e.g. পরিবারের চিকিৎসা / পারিবারিক জরুরি
            $table->boolean('is_deducted')->default(false); // মাস শেষে বেতন থেকে সমন্বয় হয়েছে কিনা
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};