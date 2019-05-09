<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkFunctionHasChapterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_function_has_chapter', function (Blueprint $table) {
            $table->unsignedInteger('workFunctionId');
            $table->foreign('workFunctionId')->references('id')->on('work_functions');
            $table->unsignedInteger('chapterId');
            $table->foreign('chapterId')->references('id')->on('chapters');
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
        Schema::dropIfExists('work_function_has_chapter');
    }
}
