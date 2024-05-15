<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmploymentReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employment_references', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->integer('applicant_id')->unsigned();
            $table->string('company_name');
            $table->string('company_email');
            $table->string("country_code")->nullable();
            $table->string('company_phone');
            $table->string('company_address')->nullable(); //single address provide by reference
            $table->json('addresses')->nullable();   //multiple addresses provided by applicant(1 to 36 addresses)
            $table->string('job_title')->nullable();
            $table->string('probation_period')->nullable();
            $table->string('contract_type')->nullable();
            $table->decimal('annual_salary', 8, 2)->default(0);
            $table->decimal('annual_bonus', 8, 2)->default(0);
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->string('signature')->nullable();
            $table->string('ref_link')->nullable();
            $table->date('fill_date')->nullable();
            $table->integer('fill_status')->default(0);
            $table->integer('status')->default(0);         //references status ['','pending','complete']
            $table->integer('agency_status')->default(0);  //references agency status ['','pending','','declined','accepted','FMD']
            $table->string('notes')->nullable();
            $table->integer('response_status')->default(0);    // this is for the response status 1 or 0
            $table->integer('response_value')->default(0);     // this is for the response value like 2/3
            $table->dateTime('last_response_time')->default(\Carbon\Carbon::now());  //last response send time
            $table->string('timezone')->default('UTC');
            $table->text('decision_text')->nullable();
            $table->text('notes_text')->nullable();
            $table->text('addresses_text')->nullable();
            $table->integer('reference_action')->default(0);
            $table->string('tenancy_id')->default(0);
            $table->timestamps();
        });

        Schema::table('employment_references', function ($table) {
            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employment_references');
    }
}
