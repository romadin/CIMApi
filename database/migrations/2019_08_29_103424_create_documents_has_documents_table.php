<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsHasDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents_has_documents', function (Blueprint $table) {
            $table->unsignedInteger('documentId');
            $table->foreign('documentId')->references('id')->on('documents');
            $table->unsignedInteger('subDocumentId');
            $table->foreign('subDocumentId')->references('id')->on('documents');
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
        Schema::dropIfExists('documents_has_documents');
    }
}
