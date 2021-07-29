<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductDiscountsTable extends Migration {

	public function up()
	{
		Schema::create('product_discounts', function(Blueprint $table) {
			$table->id();
			$table->efficientUuid('product_uuid')->index();
			$table->foreignId('currency_id')->unsigned()->nullable();
			$table->date('starting_at')->nullable();
			$table->date('finishing_at')->nullable();
			$table->decimal('value');
		});
	}

	public function down()
	{
		Schema::drop('product_discounts');
	}
}