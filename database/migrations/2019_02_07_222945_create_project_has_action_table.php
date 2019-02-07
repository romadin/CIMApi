<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectHasActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects_has_actions', function (Blueprint $table) {
            $table->unsignedInteger('projectId');
            $table->foreign('projectId')->references('id')->on('projects');
            $table->unsignedInteger('actionId');
            $table->foreign('actionId')->references('id')->on('actions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_has_action');
    }
}
