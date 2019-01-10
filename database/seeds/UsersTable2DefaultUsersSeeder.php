<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTable2DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'firstName' => 'admin',
            'lastName' => 'admin',
            'email' => 'admin@hotmail.com',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'role_id' => 1,
        ]);

        DB::table('users')->insert([
            'firstName' => 'normal',
            'insertion' => 'is',
            'lastName' => 'good',
            'email' => 'normal@hotmail.com',
            'password' => password_hash('welkom01', PASSWORD_DEFAULT),
            'role_id' => 2,
        ]);
    }
}
