<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->string("email")->unique();
            $table->string("country_code")->nullable();
            $table->string("phone")->nullable();
            $table->string("agency_confirm_link")->nullable();
            $table->integer("status")->default(0);
            $table->string("media_logo")->nullable();
            $table->string('opening_time')->nullable();
            $table->string('closing_time')->nullable();
            $table->string('address')->nullable();
            $table->integer('total_credit')->default(0);
            $table->integer('used_credit')->default(0);
            $table->integer('isDefaultSetting')->default(0);
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('google_plus')->nullable();
            $table->dateTime('last_login')->default(now()->toDateTimeString());
            $table->timestamps();
        });

        /*Schema::table('agencies', function ($table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agencies');
    }
}
