<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenanciesTable extends Migration
{
    public function up()
    {
        Schema::create('tenancies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference');
            $table->integer('agency_id')->unsigned();
            $table->integer('landlord_id')->unsigned();
            $table->integer('creator_id')->unsigned();
            $table->integer('property_id')->unsigned();
            $table->string('pro_address');
            $table->integer('parking')->default(0);
            $table->decimal('parking_cost', 12, 2)->default(0);
            $table->decimal('total_rent', 12, 2)->default(0);
            $table->string('restriction')->nullable();
            $table->string('rent_include')->nullable();
            $table->decimal('monthly_amount', 12, 2)->default(0);
            $table->decimal('deposite_amount', 12, 2)->default(0);
            $table->decimal('holding_amount', 12, 2)->default(0);
            $table->date('t_start_date')->nullable();
            $table->date('t_end_date')->nullable();
            $table->integer('no_applicant')->default(0);
            $table->date('deadline')->default(\Carbon\Carbon::now()->addDays(14)); //deadline of tenancy applicant
            $table->date('tc_date')->default(\Carbon\Carbon::now()->toDateString()); //date of tenancy applicant complete
            $table->integer('tc_status')->default(0); //tenancy applicant complete or not (0 or 1) and if all tenants has signed then its change to 1
            $table->string('notes')->nullable();
            $table->integer('status')->default(0);
            $table->integer('type')->default(0);
            $table->string('agreement')->nullable();   //agreement file link to every applicant ['','Pending','Declined','Accepted','FMD']
            $table->date('signing_date')->default(\Carbon\Carbon::now()->addDays(10));
            $table->integer('reviewer_id')->nullable();
            $table->integer('renew_tenancy')->default(0);
            $table->integer('parkingArray')->default(0);
            $table->text('notes_text')->nullable();
            $table->integer('days_to_complete')->default(0);
            $table->string('applicants_ids')->nullable();
            $table->integer('isSection21')->default(0);
            $table->integer('review_agreement')->default(0);
            $table->timestamps();
        });

        Schema::table('tenancies', function ($table) {
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenancies');
    }
}
