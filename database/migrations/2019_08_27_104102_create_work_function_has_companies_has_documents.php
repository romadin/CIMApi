<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkFunctionHasCompaniesHasDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_function_has_companies_has_documents', function (Blueprint $table) {
            $table->unsignedInteger('workFunctionId');
            $table->foreign('workFunctionId')->references('id')->on('work_functions');
            $table->unsignedInteger('companyId');
            $table->foreign('companyId')->references('id')->on('companies');
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
        Schema::dropIfExists('work_function_has_companies_has_documents');
    }
}
