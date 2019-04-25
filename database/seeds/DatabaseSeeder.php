<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('OrganisationTableSeeder');
        $this->call('RolesTableDefaultRolesSeeder');
        $this->call('UsersTable2DefaultUsersSeeder');
        $this->call('TemplatesTableSeeder');
    }
}
