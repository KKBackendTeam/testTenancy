<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->string('name');
            $table->string('l_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('email_link')->nullable();
            $table->integer('email_status')->default(0);
            $table->integer('defaltPassword')->default(0);  //first time login for the user
            $table->integer('roleStatus')->default(0);
            $table->integer('is_active')->default(1);   //user(staff member) is activate or not
            //$table->integer('staff_status')->default(0);
            $table->string('password_link')->nullable();
            $table->string('last_action')->nullable();
            $table->dateTime('last_action_date')->nullable();
            $table->string('selfie_pic')->nullable();
            $table->string("country_code")->nullable();
            $table->string('mobile')->nullable();
            $table->dateTime('last_login')->default(Carbon\Carbon::now()->toDateTimeString());
            $table->string('agreement_signature')->nullable();
            $table->dateTime('signing_time')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('timezone')->default('UTC');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('users', function ($table) {
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
        Schema::dropIfExists('users');
    }
}
