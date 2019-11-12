<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationHasModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisation_has_module', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organisationId');
            $table->foreign('organisationId')->references('id')->on('organisations');
            $table->unsignedInteger('moduleId');
            $table->foreign('moduleId')->references('id')->on('modules');
            $table->boolean('isOn')->default(false);
            $table->json('restrictions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organisation_has_module');
    }
}
