<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesHasFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies_has_folders', function (Blueprint $table) {
            $table->unsignedInteger('companyId');
            $table->foreign('companyId')->references('id')->on('companies');
            $table->unsignedInteger('folderId');
            $table->foreign('folderId')->references('id')->on('folders');
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
        Schema::dropIfExists('companies_has_folders');
    }
}
