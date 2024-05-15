<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenancy_id')->unsigned();
            $table->integer('applicant_id')->unsigned();
            $table->integer('agency_id')->unsigned();
            $table->dateTime('date')->nullable();
            $table->string('amount')->nullable();
            $table->timestamps();

            $table->foreign('tenancy_id')->references('id')->on('tenancies')->onDelete('cascade');
            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
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
        Schema::dropIfExists('payment_schedules');
    }
}
