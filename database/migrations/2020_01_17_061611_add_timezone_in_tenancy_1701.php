    <?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimezoneInTenancy1701 extends Migration
{
   public function up()
    {
        Schema::table('tenancies', function (Blueprint $table) {
            $table->string('timezone')->default('UTC');
        });
    }

    public function down()
    {
        Schema::table('tenancies', function ($table) {
            $table->dropColumn(['timezone']);
        });
    }
}
