<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("enterprise_id");
            $table->unsignedInteger("meter_id");
            $table->string("name");
            $table->string('database')->nullable();            
            $table->string('table_name')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
            
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->foreign('meter_id')->references('id')->on('energy_meters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_configurations');
    }
}
