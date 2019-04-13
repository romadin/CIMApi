<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_image', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('documentId');
            $table->string('imageName');
            $table->string('extension');
            $table->string('pathName');
            $table->string('size');
            $table->binary('image');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE `document_image`  MODIFY COLUMN `image` mediumblob");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_image');
    }
}
