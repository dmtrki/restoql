<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid')->index();
            $table->efficientUuid('fingerprint_uuid')->nullable();
            $table->efficientUuid('customer_uuid')->nullable()->index();
            $table->unsignedBigInteger('product_id');
            $table->efficientUuid('variation_id')->nullable();
            $table->unsignedDecimal('quantity', $precision = 8, $scale = 2)->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}
