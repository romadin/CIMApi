<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkFunctionHasDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_function_has_document', function (Blueprint $table) {
            $table->unsignedInteger('workFunctionId');
            $table->foreign('workFunctionId')->references('id')->on('work_functions');
            $table->unsignedInteger('documentId');
            $table->foreign('documentId')->references('id')->on('documents');
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
        Schema::dropIfExists('work_function_has_document');
    }
}
