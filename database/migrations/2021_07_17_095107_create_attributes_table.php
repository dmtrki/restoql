<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttributesTable extends Migration {

	public function up()
	{
		Schema::create('attributes', function(Blueprint $table) {
			$table->id();
			$table->efficientUuid('uuid');
			$table->string('slug')->unique()->index();
			$table->string('type', 16)->nullable();
			$table->string('unit', 64)->nullable();
			$table->string('title', 128);
			$table->unsignedInteger('attribute_group_id')->index()->nullable();
			$table->integer('order')->default(0);
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('attributes');
	}
}