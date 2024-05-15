<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnTypeQuaterlyReferenceInTableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quarterly_references', function (Blueprint $table) {
            $table->dropColumn('qu_doc');
        });

        Schema::table('quarterly_references', function (Blueprint $table) {
            $table->json('qu_doc')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quarterly_references', function (Blueprint $table) {
            $table->string('qu_doc');
            $table->dropColumn('qu_doc');
        });
    }
}
