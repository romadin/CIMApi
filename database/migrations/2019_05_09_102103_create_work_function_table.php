<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkFunctionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_functions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('isMainFunction')->default(false);
            $table->unsignedInteger('order');
            $table->unsignedInteger('templateId')->nullable(true);
            $table->unsignedInteger('projectId')->nullable(true);
            $table->foreign('templateId')->references('id')->on('templates');
            $table->foreign('projectId')->references('id')->on('projects');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_functions');
    }
}
