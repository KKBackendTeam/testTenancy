<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandlordReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landlord_references', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->string('name');
            $table->integer('applicant_id')->unsigned();
            $table->string('address')->nullable();   //single address provide by reference
            $table->json('addresses')->nullable();   //multiple addresses provided by applicant(1 to 36 addresses)
            $table->string('email');
            $table->string("country_code")->nullable();
            $table->string('phone');
            $table->integer('rent_price')->nullable();
            $table->string('paid_status')->nullable();
            $table->string('frequent_status')->nullable();
            $table->string('arrears_status')->nullable();
            $table->integer('paid_arrears')->default(0);
            $table->string('damage_status')->nullable();
            $table->string('damage_detail')->nullable();
            $table->date('t_s_date')->nullable();
            $table->date('t_e_date')->nullable();
            $table->string('moveout_status')->nullable();
            $table->string('tenant_status')->nullable();
            $table->string('why_not')->nullable();
            $table->string('company_name')->nullable();
            $table->string('position')->nullable();
            $table->string('signature')->nullable();
            $table->date('fill_date')->nullable();
            $table->integer('fill_status')->default(0);
            $table->integer('status')->default(0);         //references status ['pending','complete']
            $table->integer('agency_status')->default(0);  //references agency status ['pending','declined','accepted','FMD']
            $table->string('ref_link')->nullable();
            $table->string('notes')->nullable();
            $table->integer('response_status')->default(0);    // this is for the response status 1 or 0
            $table->integer('response_value')->default(0);     // this is for the response value like 2/3
            $table->dateTime('last_response_time')->default(\Carbon\Carbon::now());  //last response send time
            $table->string('timezone')->default('UTC');
            $table->text('decision_text')->nullable();
            $table->text('notes_text')->nullable();
            $table->text('addresses_text')->nullable();
            $table->decimal('rent_price_value', 8, 2)->default(0);
            $table->decimal('paid_arrears_value', 8, 2)->default(0);
            $table->integer('reference_action')->default(0);
            $table->string('tenancy_id')->default(0);
            $table->timestamps();
        });

        Schema::table('landlord_references', function ($table) {
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
        Schema::dropIfExists('landlord_references');
    }
}
