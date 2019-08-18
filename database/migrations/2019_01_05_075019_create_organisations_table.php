<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::defaultStringLength(191);

        Schema::create('organisations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('primaryColor')->default('#002060');
            $table->string('secondaryColor')->default('#ffc000');
            $table->unsignedInteger('maxUsers')->default(5);
            $table->unsignedInteger('templatesNumber')->default(1);
            $table->binary('logo')->nullable(true);
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `organisations`  MODIFY COLUMN `logo` mediumblob");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organisations');
    }
}
