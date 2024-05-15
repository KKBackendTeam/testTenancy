<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicantRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applicant_requirements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->boolean('must_be_18')->default(false);
            $table->boolean('ae_less3_must_g')->default(true);
            $table->boolean('ae_least2')->default(true);
            $table->boolean('as_ukr_must_ukg')->default(true);
            $table->boolean('as_ir_pay_pqa')->default(true);
            $table->boolean('a_not_ukg_pqa')->default(true);
            $table->timestamps();
        });

        Schema::table('applicant_requirements', function ($table) {
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
        Schema::dropIfExists('applicant_requirements');
    }
}
