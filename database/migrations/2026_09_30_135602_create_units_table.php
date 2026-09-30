<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // e.g. কেজি (Kilogram), বস্তা (Sack)
            $table->string('short_code', 20)->unique();     // e.g. kg, gm, sack, ltr
            $table->boolean('allow_decimal')->default(true); // true for 1.25 kg, false for 1 piece
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->nullOnDelete(); // Reference unit
            $table->decimal('conversion_rate', 12, 4)->nullable(); // e.g. 1 Sack = 50 Kg, 1 Kg = 1000 gm
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};