<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid')->index();
            $table->efficientUuid('customer_uuid')->nullable();
            $table->efficientUuid('transaction_uuid')->nullable();
            $table->json('details');
            $table->unsignedDecimal('total', $precision = 8, $scale = 2);
            $table->tinyInteger('status_code')->default(1);
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
        Schema::dropIfExists('orders');
    }
}
