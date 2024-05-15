<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOnTextForSpecificArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('text_for_specific_areas', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('text_for_specific_areas', function ($table) {
            $table->dropColumn(['name']);
            $table->dropColumn(['type']);
        });
    }
}
