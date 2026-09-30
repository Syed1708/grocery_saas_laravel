<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->nullable()->constrained('product_sections')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('name');                         // বাংলা নাম (e.g. রূপচাঁদা সয়াবিন তেল ৫ লিটার)
            $table->string('name_en')->nullable();          // English Name (e.g. Rupchanda Soyabean Oil 5L)
            $table->string('barcode', 100)->unique();       // Barcode Gun scan or auto-generated
            $table->string('sku', 50)->unique();            // Stock Keeping Unit
            $table->decimal('purchase_price', 12, 2);       // ক্রয়মূল্য (Cost)
            $table->decimal('selling_price', 12, 2);        // খুচরা বিক্রয়মূল্য (MRP)
            $table->decimal('wholesale_price', 12, 2)->nullable(); // পাইকারি মূল্য
            $table->decimal('vat_percent', 5, 2)->default(0);      // ভ্যাট %
            $table->date('expiry_date')->nullable();        // মেয়াদোত্তীর্ণের তারিখ
            $table->decimal('alert_quantity', 10, 2)->default(5.00); // কম স্টক সতর্কতা
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};