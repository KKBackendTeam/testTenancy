<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnOnLandlordReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landlord_references', function (Blueprint $table) {
            $table->string('post_code')->nullable();
            $table->string('street')->nullable();
            $table->string('town')->nullable();
            $table->string('country')->nullable();
        });

        Schema::table('landlord_references', function($table) {
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
        Schema::table('landlord_references', function($table) {
            $table->dropColumn('post_code');
            $table->dropColumn('street');
            $table->dropColumn('town');
            $table->dropColumn('country');
        });

        Schema::table('landlord_references', function($table) {
            $table->string('address');
        });
    }
}
