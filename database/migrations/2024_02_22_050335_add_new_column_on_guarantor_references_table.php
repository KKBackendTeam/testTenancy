<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnOnGuarantorReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guarantor_references', function (Blueprint $table) {
            $table->string('post_code')->nullable();
            $table->string('street')->nullable();
            $table->string('town')->nullable();
            $table->string('country')->nullable();
        });

        Schema::table('guarantor_references', function($table) {
            $table->dropColumn('student_details');
            $table->dropColumn('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guarantor_references', function($table) {
            $table->dropColumn('post_code');
            $table->dropColumn('street');
            $table->dropColumn('town');
            $table->dropColumn('country');
        });

        Schema::table('guarantor_references', function($table) {
            $table->string('student_details');
            $table->string('address');
        });
    }
}
