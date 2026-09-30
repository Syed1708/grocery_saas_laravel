<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');                        // e.g. ওয়ালটন ৩০০ লিটার ডিপ ফ্রিজ
            $table->string('asset_code', 50)->unique();    // e.g. AST-001
            $table->string('serial_number')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 12, 2);       // Original price in BDT
            $table->decimal('current_value', 12, 2);       // Valuation after depreciation
            $table->enum('condition', ['good', 'maintenance', 'damaged', 'disposed'])->default('good');
            $table->text('notes')->nullable();             // Warranty info, servicing contacts
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_assets');
    }
};