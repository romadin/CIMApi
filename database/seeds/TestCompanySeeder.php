<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'firstName' => 'Piet',
                'lastName' => 'Test',
                'email' => 'piet@hotmail.com',
                'function' => 'manager',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'role_id' => 2,
                'phoneNumber' => '0611111112',
                'organisationId' => 2,
                'companyId' => 1
            ],
            [
                'firstName' => 'Jan',
                'lastName' => 'Test',
                'email' => 'jan@hotmail.com',
                'function' => 'manager',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'role_id' => 2,
                'phoneNumber' => '0611111113',
                'organisationId' => 2,
                'companyId' => 1
            ],
            [
                'firstName' => 'Romario',
                'lastName' => 'Test',
                'email' => 'romario@hotmail.com',
                'function' => 'manager',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'role_id' => 2,
                'phoneNumber' => '0611111114',
                'organisationId' => 2,
                'companyId' => 3
            ]

        ]);

        DB::table('projects')->insert([
            [
                'name' => 'project 1 met user 1',
                'organisationId' => 2,
            ],
            [
                'name' => 'project 2 met user 2',
                'organisationId' => 2,
            ],
            [
                'name' => 'project 3 met user 3',
                'organisationId' => 2,
            ],
        ]);

        DB::table('work_functions_has_companies')->insert([
            [
                'workFunctionId' => 1,
                'companyId' => 1,
            ],
            [
                'workFunctionId' => 1,
                'companyId' => 2,
            ],
            [
                'workFunctionId' => 1,
                'companyId' => 5,
            ],
            [
                'workFunctionId' => 2,
                'companyId' => 1,
            ],
        ]);
        DB::table('users_has_projects')->insert([
            [
                'userId' => 1,
                'projectId' => 1,
            ],
            [
                'userId' => 2,
                'projectId' => 2,
            ],
            [
                'userId' => 3,
                'projectId' => 3,
            ],

        ]);


    }
}
