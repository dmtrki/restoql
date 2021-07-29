<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCitiesTable extends Migration {

	public function up()
	{
		Schema::create('cities', function(Blueprint $table) {
			$table->id();
			$table->efficientUuid('uuid')->index();
			$table->string('slug')->unique();
			$table->string('title', 128);
			$table->json('details')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('cities');
	}
}