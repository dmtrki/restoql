<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFingerprintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fingerprints', function (Blueprint $table) {
          $table->id();
          $table->efficientUuid('uuid')->index();
          $table->string('hash')->nullable();
          $table->ipAddress('ip')->nullable();
          $table->string('user_agent')->nullable();
          $table->string('timezone')->nullable();
          $table->string('platform')->nullable();
          $table->string('screen')->nullable();
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fingerprints');
    }
}
