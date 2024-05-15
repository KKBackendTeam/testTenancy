<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicantsTable extends Migration
{

    public function up()
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenancy_id')->unsigned();
            $table->integer('applicant_id')->unsigned();
            $table->integer('agency_id')->unsigned();
            $table->integer('creator_id')->unsigned();
            $table->string('country_code')->nullable();
            $table->string('doc_type')->nullable();
            $table->string('front_doc')->nullable();
            $table->string('back_doc')->nullable();
            $table->string('selfie_pic')->nullable();
            $table->string('signature')->nullable();
            $table->string('agreement_signature')->nullable();
            $table->string('app_url')->nullable();
            $table->integer('type')->default(0);   //type student=1 or employment=2 or neither = 3
            $table->integer('log_status')->default(0); //privacy statement fill or not
            $table->integer('ref_status')->default(0);   //their ref fill form status ['','pending','complete']
            $table->integer('ref_agency_status')->default(0);   //their ref status ['','Pending','Declined','Accepted','FMD']
            $table->string('agreement')->nullable();   //agreement file link to every applicant ['','Pending','Declined','Accepted','FMD']
            $table->integer('status')->default(0);       //status 1 to 11
            $table->integer('ta_status')->default(0);    //application is complete or not
            $table->integer('review_status')->default(0);     //applicant is reviewed by agency or not (0 or 1)
            $table->integer('response_status')->default(0);  //response fill or not from chasing
            $table->integer('response_value')->default(0);  //response value like 1/3 from chasing
            $table->dateTime('last_response_time')->default(\Carbon\Carbon::now());  //last response send time
            $table->string('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('is_complete')->default(0); //flag to agency for incomplete
            $table->dateTime('signing_time')->nullable();
            $table->text('addresses_text')->nullable();
            $table->json('addresses')->nullable();
            $table->integer('total_references')->default(0);
            $table->integer('fill_references')->default(0);
            $table->integer('reference_tracker')->default(0);
            $table->integer('review_agreement')->default(0);
            $table->integer('level_1')->default(0);
            $table->integer('level_2')->default(0);
            $table->integer('level_3')->default(0);
            $table->integer('level_4')->default(0);
            $table->text('notes_text')->nullable();
            $table->date('right_to_rent')->nullable();
            $table->string('passport_document')->nullable();
            $table->string('selfie_passport_document')->nullable();
            $table->string('selfie_resident_card')->nullable();
            $table->integer('step')->default(0);   //save steps data
            $table->integer('renew_status')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('applicants', function ($table) {
            $table->foreign('applicant_id')->references('id')->on('applicantbasics')->onDelete('cascade');
        });
        Schema::table('applicants', function ($table) {
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
        Schema::dropIfExists('applicants');
    }
}
