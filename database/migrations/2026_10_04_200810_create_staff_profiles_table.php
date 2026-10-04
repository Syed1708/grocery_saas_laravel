<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Optional login account link
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('name');                         // কর্মচারীর নাম
            $table->string('phone', 20)->unique();
            $table->string('emergency_phone', 20)->nullable();
            $table->string('nid_number', 50)->nullable();   // জাতীয় পরিচয়পত্র নম্বর
            $table->string('designation');                  // পদবী (e.g. ক্যাশিয়ার, সেলসম্যান, ডেলিভারি বয়)
            $table->date('joining_date');
            $table->decimal('basic_salary', 12, 2);         // মূল মাসিক বেতন (৳)
            $table->decimal('daily_allowance', 10, 2)->default(0.00); // দৈনিক নাস্তা/খাবার ভাতা
            $table->decimal('overtime_rate', 10, 2)->default(0.00);   // প্রতি ঘণ্টা ওভারটাইম রেট
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};