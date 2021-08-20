<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid')->index();
			$table->string('slug')->unique()->index();
			// $table->efficientUuid('category_uuid')->nullable()->index();
			// $table->efficientUuid('manufacturer_uuid')->nullable();
            $table->unsignedBigInteger('product_category_id')->nullable()->index();
			$table->unsignedBigInteger('manufacturer_id')->nullable();
			$table->tinyInteger('status_code')->default('1');
			$table->string('title', 255);
			$table->decimal('price', 10,2)->nullable();
			$table->decimal('rating', 2,2)->nullable();
			$table->bigInteger('views')->default('0');
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
        Schema::dropIfExists('products');
    }
}
