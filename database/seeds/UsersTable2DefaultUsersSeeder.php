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
            'function' => 'manager',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'role_id' => 1,
            'phoneNumber' => '0611111111',
            'organisationId' => 1,
        ]);

        DB::table('users')->insert([
            'firstName' => 'normal',
            'insertion' => 'is',
            'lastName' => 'good',
            'email' => 'normal@hotmail.com',
            'function' => 'werknemer',
            'password' => password_hash('welkom01', PASSWORD_DEFAULT),
            'role_id' => 2,
            'phoneNumber' => '0622222222',
            'organisationId' => 1
        ]);
    }
}
