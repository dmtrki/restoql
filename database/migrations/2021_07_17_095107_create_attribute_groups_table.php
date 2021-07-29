<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttributeGroupsTable extends Migration {

	public function up()
	{
		Schema::create('attribute_groups', function(Blueprint $table) {
			$table->id();
			$table->string('slug', 128)->unique()->index();
			$table->string('type', 32)->nullable()->index();
			$table->string('title', 128);
			$table->integer('order')->default(0);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('attribute_groups');
	}
}