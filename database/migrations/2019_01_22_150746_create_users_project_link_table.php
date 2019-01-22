<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersProjectLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_has_projects', function (Blueprint $table) {
            $table->unsignedInteger('userId');
            $table->foreign('userId')->references('id')->on('users');
            $table->unsignedInteger('projectId');
            $table->foreign('projectId')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_has_projects');
    }
}
