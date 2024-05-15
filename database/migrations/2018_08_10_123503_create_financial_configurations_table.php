<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->decimal('amount', 8, 2)->default(0);    //2-3 time
            $table->integer('period')->default(0);   //weekly or monthly
            $table->integer('type')->default(0);     //per person or per tenancy
            $table->integer('method')->default(0);    //method to calculate
            $table->timestamps();
        });

        Schema::table('financial_configurations', function ($table) {
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
        Schema::dropIfExists('financial_configurations');
    }
}
