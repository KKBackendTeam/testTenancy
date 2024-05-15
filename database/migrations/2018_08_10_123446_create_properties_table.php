<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id')->unsigned();
            $table->integer('landlord_id')->unsigned();
            $table->string('property_ref');   //property References
            $table->integer('creator_id')->default(0);
            $table->string('post_code');
            $table->string('street');
            $table->string('town');
            $table->string('country');
            $table->integer('status')->default(0);
            $table->integer('parkingToggle')->default(0);
            $table->decimal('parking_cost', 12, 2)->default(0);
            $table->decimal('total_rent', 12, 2)->default(0);
            $table->integer('parkingArray')->default(0);
            $table->integer('bedroom')->default(0);
            $table->string('restriction')->nullable();
            $table->string('rent_include')->nullable();
            $table->integer('hasGas')->default(0);
            $table->string('gas_expiry_date')->nullable();
            $table->string('gas_certificate')->nullable();
            $table->string('epc_expiry_date')->nullable();
            $table->string('epc_certificate')->nullable();
            $table->string('electric_expiry_date')->nullable();
            $table->string('electric_certificate')->nullable();
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->decimal('deposite_amount', 12, 2)->default(0);
            $table->decimal('holding_fee_amount', 12, 2)->default(0);
            $table->date('available_from')->default(\Carbon\Carbon::now()->toDateString());
            $table->integer('previous_status')->default(0);
            $table->timestamps();
        });

        Schema::table('properties', function ($table) {
            $table->foreign('landlord_id')->references('id')->on('landloards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties');
    }
}
