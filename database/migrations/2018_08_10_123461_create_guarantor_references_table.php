<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuarantorReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guarantor_references', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->integer('applicant_id')->unsigned();
            $table->string('name');
            $table->string('email');
            $table->string("country_code")->nullable();
            $table->string('phone');
            $table->string('address')->nullable();   //single address provide by reference
            $table->json('addresses')->nullable();   //multiple addresses provided by applicant(1 to 36 addresses)
            $table->string('owner')->nullable();
            $table->string('relationship')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('hr_email')->nullable();
            $table->integer('least_income')->default(0);
            $table->string('address_proof')->nullable();
            $table->string('id_proof')->nullable();
            $table->string('financial_proof')->nullable();
            $table->string('signature')->nullable();
            $table->date('fill_date')->nullable();         //references fill form date
            $table->integer('fill_status')->default(0);    //references fill their form or not 1/0
            $table->integer('status')->default(0);         //references status ['pending','complete']
            $table->integer('agency_status')->default(0);  //references agency status ['pending','declined','accepted','FMD']
            $table->string('ref_link')->nullable();
            $table->string('notes')->nullable();
            $table->integer('response_status')->default(0);    // this is for the response status 1 or 0
            $table->integer('response_value')->default(0);     // this is for the response value like 2/3
            $table->dateTime('last_response_time')->default(\Carbon\Carbon::now());  //last response send time
            $table->integer('is_living_uk')->default(0);
            $table->string('timezone')->default('UTC');
            $table->integer('is_eighteen')->default(0);
            $table->text('decision_income_text')->nullable();
            $table->text('decision_id_text')->nullable();
            $table->text('decision_address_text')->nullable();
            $table->text('notes_text')->nullable();
            $table->text('addresses_text')->nullable();
            $table->decimal('guarantor_income', 12, 2)->default(0);
            $table->integer('decision_income_action')->default(0);
            $table->integer('decision_id_action')->default(0);
            $table->integer('decision_address_action')->default(0);
            $table->string('tenancy_id')->default(0);
            $table->timestamps();
        });

        Schema::table('guarantor_references', function ($table) {
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
        Schema::dropIfExists('guarantor_references');
    }
}
