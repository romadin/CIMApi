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
            $table->unsignedInteger('templateId');
            $table->foreign('templateId')->references('id')->on('templates');
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
