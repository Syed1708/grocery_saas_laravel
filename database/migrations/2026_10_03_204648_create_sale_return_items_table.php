<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained('sale_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('refund_price', 12, 2);
            $table->enum('item_condition', ['restock', 'damaged'])->default('restock'); // Restock to branch vs loss
            $table->timestamps();
        });
    }

        public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
