<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInTenancies1309 extends Migration
{
    public function up()
    {
        Schema::table('tenancies', function (Blueprint $table) {
            $table->date('generated_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tenancies', function ($table) {
            $table->dropColumn(['generated_date']);
        });
    }
}
