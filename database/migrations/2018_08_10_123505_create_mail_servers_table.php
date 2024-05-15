<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_servers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->string('driver');
            $table->string('host');
            $table->string('port');
            $table->string('from_name');
            $table->string('from_address');
            $table->string('encryption');
            $table->string('username');
            $table->string('password');
            $table->timestamps();
        });

        Schema::table('mail_servers', function ($table) {
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
        Schema::dropIfExists('mail_servers');
    }
}
