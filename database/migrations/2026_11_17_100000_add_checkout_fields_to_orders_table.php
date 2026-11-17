<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('phone', 32)->nullable()->after('shipping_address');
            $table->string('delivery_method', 20)->default('courier')->after('phone');
            $table->string('payment_method', 20)->default('card')->after('delivery_method');
            $table->decimal('delivery_price', 10, 2)->default(0)->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'delivery_method', 'payment_method', 'delivery_price']);
        });
    }
};
