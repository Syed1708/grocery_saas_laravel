<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->enum('type', ['taken', 'given']); // taken = ঋণ গ্রহণ (Liability), given = ধার প্রদান (Asset)
            $table->string('loan_no', 100)->unique();
            $table->string('person_name');
            $table->string('phone', 20)->nullable();
            $table->decimal('amount', 14, 2);
            $table->decimal('total_paid', 14, 2)->default(0.00);
            $table->decimal('remaining_amount', 14, 2);
            $table->date('loan_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['active', 'paid'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};