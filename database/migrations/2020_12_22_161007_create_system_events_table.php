<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_events', function (Blueprint $table) {
            $table->id();
          $table->efficientUuid('uuid')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('type_code')->default(1);
            $table->tinyInteger('status_code')->nullable();
$table->json('details')->nullable();
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
        Schema::dropIfExists('system_events');
    }
}
