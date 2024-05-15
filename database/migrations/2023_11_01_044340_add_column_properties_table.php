<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function($table) {
            $table->string('hmo')->nullable();
            $table->string('hmo_expiry_date')->nullable();
            $table->string('hmo_certificate')->nullable();
            $table->string('fire_alarm')->nullable();
            $table->string('fire_alarm_expiry_date')->nullable();
            $table->string('fire_alarm_certificate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function($table) {
            $table->dropColumn('hmo');
            $table->dropColumn('hmo_expiry_date');
            $table->dropColumn('hmo_certificate');
            $table->dropColumn('fire_alarm');
            $table->dropColumn('fire_alarm_expiry_date');
            $table->dropColumn('fire_alarm_certificate');
        });
    }
}
