<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChasingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chasings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->boolean('sms')->default(false);
            $table->boolean('email')->default(true);
            $table->boolean('cc')->default(false);
            $table->integer('stalling_time')->unsigned()->default(0);
            $table->integer('response_time')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::table('chasings', function ($table) {
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chasings');
    }
}
