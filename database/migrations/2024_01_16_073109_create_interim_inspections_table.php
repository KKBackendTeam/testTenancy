<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterimInspectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interim_inspections', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenancy_id')->unsigned();
            $table->integer('agency_id')->unsigned();
            $table->string('reference')->nullable();
            $table->string('address')->nullable();
            $table->string('inspection_month')->nullable();
            $table->string('inspection_date')->nullable();
            $table->string('email_date')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_done')->nullable()->default(false);
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interim_inspections');
    }
}
