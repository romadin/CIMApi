<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkFunctionHasHeadlineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_function_has_headline', function (Blueprint $table) {
            $table->unsignedInteger('workFunctionId');
            $table->foreign('workFunctionId')->references('id')->on('work_functions');
            $table->unsignedInteger('headlineId');
            $table->foreign('headlineId')->references('id')->on('headlines');
            $table->unsignedInteger('order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_function_has_headline');
    }
}
