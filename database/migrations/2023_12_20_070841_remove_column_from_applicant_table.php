<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnFromApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicants', function($table) {
            $table->dropColumn('front_doc');
            $table->dropColumn('back_doc');
            $table->dropColumn('selfie_resident_card');
            $table->dropColumn('family_addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function($table) {
            $table->string('front_doc')->nullable();
            $table->string('back_doc')->nullable();
            $table->string('selfie_resident_card')->nullable();
            $table->json('family_addresses')->nullable();
        });
    }
}