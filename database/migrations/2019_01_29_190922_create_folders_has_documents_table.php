<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoldersHasDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folders_has_documents', function (Blueprint $table) {
            $table->unsignedInteger('folderId');
            $table->foreign('folderId')->references('id')->on('folders');
            $table->unsignedInteger('documentId');
            $table->foreign('documentId')->references('id')->on('documents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folders_has_documents');
    }
}
