<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicantbasicsTable extends Migration
{
    public function up()
    {
        Schema::create('applicantbasics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenancy_id')->unsigned();
            $table->integer('agency_id')->unsigned();
            $table->string('app_name');
            $table->string('m_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('email')->unique();
            $table->string('dob')->nullable();
            $table->string('password');
            $table->string('temporary_password')->nullable();
            $table->string('country_code')->nullable();
            $table->string('app_mobile');
            $table->string('selfie_pic')->nullable();
            $table->string('password_link')->nullable();
            $table->string('app_ni_number')->nullable();
            $table->integer('renew_status')->default(0);
            $table->string('timezone')->default('UTC');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('applicantbasics');
    }
}
