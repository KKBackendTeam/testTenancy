<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->text('register_email')->nullable();
            $table->timestamps();
        });

        Schema::table('mail_configurations', function ($table) {
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
        Schema::dropIfExists('mail_configurations');
    }
}
