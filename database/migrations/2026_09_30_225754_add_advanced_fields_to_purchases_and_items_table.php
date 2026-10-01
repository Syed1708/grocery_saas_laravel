<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('total_quantity', 12, 2)->default(0.00)->after('subtotal');
            $table->decimal('vat_percent', 5, 2)->default(0.00)->after('transport_cost');
            $table->decimal('vat_amount', 12, 2)->default(0.00)->after('vat_percent');
            $table->decimal('previous_due', 12, 2)->default(0.00)->after('grand_total');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('commission', 12, 2)->default(0.00)->after('unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['total_quantity', 'vat_percent', 'vat_amount', 'previous_due']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('commission');
        });
    }
};