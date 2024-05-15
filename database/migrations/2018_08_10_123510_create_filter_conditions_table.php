<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilterConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filter_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned(); //agency_id
            $table->string('code');//filter_code
            $table->string('name');//filter name
            $table->text('condition');// filter condition json
            $table->timestamps();
        });


        Schema::table('filter_conditions', function ($table) {
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
        Schema::dropIfExists('filter_conditions');
    }
}
