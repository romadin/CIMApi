<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoldersHasFolderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folders_has_folders', function (Blueprint $table) {
            $table->unsignedInteger('folderId');
            $table->foreign('folderId')->references('id')->on('folders');
            $table->unsignedInteger('folderSubId');
            $table->foreign('folderSubId')->references('id')->on('folders');
            $table->unsignedInteger('order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folders_has_folders');
    }
}
