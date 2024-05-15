<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandloardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landloards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id');
            $table->integer('creator_id')->unsigned();
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('post_code')->nullable();
            $table->string('street')->nullable();
            $table->string('town')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->unique();
            //$table->string('self_ref')->unique();
            $table->timestamps();
        });

        Schema::table('landloards', function ($table) {
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landloards');
    }
}
