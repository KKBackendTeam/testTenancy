<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuarantorRefOtherDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guarantor_ref_other_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('guarantor_ref_id');
            $table->foreign('guarantor_ref_id')->references('id')->on('guarantor_references')->onDelete('cascade');
            $table->string('doc')->nullable();
            $table->integer('decision_action')->default(0);
            $table->text('decision_text')->nullable();
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
        Schema::dropIfExists('guarantor_ref_other_documents');
    }
}
