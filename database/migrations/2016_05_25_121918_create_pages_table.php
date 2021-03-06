<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // TODO: use JSON data type for 'extras' instead of string
        Schema::create('pages', function (Blueprint $table) {
          $table->id();
          $table->unsignedBigInteger('site_id')->nullable();
          $table->string('template');
          $table->string('name');
          $table->string('title');
          $table->string('slug');
          $table->string('url');
          $table->text('content')->nullable();
          $table->json('extras')->nullable();
          $table->json('blocks')->nullable();
          NestedSet::columns($table);
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
        Schema::drop('pages');
    }
}
