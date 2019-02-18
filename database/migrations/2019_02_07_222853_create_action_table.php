<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('code');
            $table->string('description');
            $table->string('actionHolder')->nullable(true);
            $table->unsignedInteger('week')->nullable(true);
            $table->string('comments')->nullable(true);
            $table->boolean('isDone')->default(false);
            $table->unsignedInteger('projectId');
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
        Schema::dropIfExists('actions');
    }
}
