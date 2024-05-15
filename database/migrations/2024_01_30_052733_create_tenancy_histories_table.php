<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenancyHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenancy_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->integer('tenancy_id')->unsigned();
            $table->string('agreement_type')->nullable();
            $table->string('agreement')->nullable();
            $table->date('signing_date')->nullable();
            $table->timestamps();
        });
        Schema::table('tenancy_histories', function ($table) {
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
        Schema::table('tenancy_histories', function ($table) {
            $table->foreign('tenancy_id')->references('id')->on('tenancies')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenancy_histories');
    }
}
