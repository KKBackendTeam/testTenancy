<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuarterlyReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quarterly_references', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->integer('applicant_id')->unsigned();
            $table->decimal('close_bal', 15, 2)->default(0);
            $table->string('qu_doc');
            $table->text('notes')->nullable();
            $table->date('fill_date')->nullable();         //references fill form date
            $table->integer('fill_status')->default(0);    //references fill their form or not 1/0
            $table->integer('status')->default(0);         //references status ['pending','complete']
            $table->integer('agency_status')->default(0);
            $table->text('decision_text')->nullable();
            $table->integer('reference_action')->default(0);
            $table->string('type')->nullable();
            $table->string('tenancy_id')->default(0);
            $table->timestamps();
        });

        Schema::table('quarterly_references', function ($table) {
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
        Schema::dropIfExists('quarterly_references');
    }
}
