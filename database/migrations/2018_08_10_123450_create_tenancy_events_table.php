<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenancyEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenancy_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id');
            $table->integer('tenancy_id')->unsigned();
            $table->string('event_type')->nullable();
            $table->string('description')->nullable();
            $table->string('creator')->nullable();
            $table->string('applicants')->nullable();
            $table->json('details')->nullable();
            $table->dateTime('date')->default(\Carbon\Carbon::now());
            $table->timestamps();
        });

        Schema::table('tenancy_events', function ($table) {
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
        Schema::dropIfExists('tenancy_events');
    }
}
