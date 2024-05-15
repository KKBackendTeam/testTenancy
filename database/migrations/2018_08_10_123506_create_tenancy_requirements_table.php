<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenancyRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenancy_requirements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->boolean('no_pets')->default(false);
            $table->boolean('no_student')->default(false);
            $table->boolean('no_family')->default(false);
            $table->boolean('no_professional')->default(false);
            $table->integer('tenancy_max_length')->unsigned()->default(1);
            $table->boolean('start_month')->default(false);
            $table->boolean('end_month')->default(false);
            $table->timestamps();
        });

        Schema::table('tenancy_requirements', function ($table) {
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
        Schema::dropIfExists('tenancy_requirements');
    }
}
