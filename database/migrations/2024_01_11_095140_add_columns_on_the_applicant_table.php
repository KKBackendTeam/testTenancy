<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsOnTheApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicants', function($table) {
            $table->boolean('is_paused')->nullable()->default(false);
            $table->string('pause_end_date')->nullable();
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
            $table->dropColumn('is_paused');
            $table->dropColumn('pause_end_date');
        });
    }
}
