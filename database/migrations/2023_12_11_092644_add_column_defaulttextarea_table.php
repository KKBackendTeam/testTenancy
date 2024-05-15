<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDefaulttextareaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('default_text_for_specific_areas', function($table) {
            $table->string('type')->nullable()->after('id');
            $table->string('name')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('default_text_for_specific_areas', function($table) {
            $table->dropColumn('type');
            $table->dropColumn('name');
        });
    }
}
