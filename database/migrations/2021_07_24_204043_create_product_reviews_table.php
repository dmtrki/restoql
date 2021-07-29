<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid');
            $table->efficientUuid('product_uuid')->index();
            $table->efficientUuid('customer_uuid');
            $table->tinyInteger('status_code')->default(0);
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('cascade');
            $table->unsignedInteger('rating')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_reviews');
    }
}
