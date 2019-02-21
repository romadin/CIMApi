<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstName');
            $table->string('insertion')->nullable($value = true);
            $table->string('lastName');
            $table->string('email', 250)->unique();
            $table->string('function');
            $table->string('password')->default(password_hash(random_bytes(10), PASSWORD_DEFAULT));
            $table->binary('image')->nullable(true);
            $table->string('token')->nullable(true);

            $table->unsignedInteger('role_id')->default(2);

            $table->timestamps();
        });

        DB::statement("ALTER TABLE `users`  MODIFY COLUMN `image` mediumblob");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
