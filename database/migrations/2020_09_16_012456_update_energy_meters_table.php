<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEnergyMetersTable extends Migration
{
    # @Leo W* creamos un nuevo campo de tipo json para guardar las BD de produccion de un contador
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('energy_meters', function (Blueprint $table) {
            $table->json('production_databases')->nullable()->after('group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('energy_meters', 'production_databases'))
        {
            Schema::table('energy_meters', function (Blueprint $table) {
                $table->dropColumn('production_databases');
            });    
        }
    }
    //alter table `energy_meters` add `production_databases` json null after `group_id`
}
