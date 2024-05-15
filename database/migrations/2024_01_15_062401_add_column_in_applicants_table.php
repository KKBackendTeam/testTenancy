<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicants', function($table) {
            $table->json('payment_schedule')->nullable();
            $table->string('total_amount_paid')->nullable();
            $table->string('total_amount')->nullable();
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
            $table->dropColumn('payment_schedule');
            $table->dropColumn('total_amount_paid');
            $table->dropColumn('total_amount');
        });
    }
}
