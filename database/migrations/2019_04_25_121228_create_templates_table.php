<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organisationId');
            $table->string('name');
            $table->json('folders')->nullable(true);
            $table->json('subFolders')->nullable(true);
            $table->json('documents')->nullable(true);
            $table->json('subDocuments')->nullable(true);
            $table->timestamps();

            $table->foreign('organisationId')->references('id')->on('organisations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('templates');
    }
}
