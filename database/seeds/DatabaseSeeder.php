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
        $this->call('WorkFunctionsTableSeeder');
        $this->call('HeadlinesTableSeeder');
        $this->call('WorkFunctionHasHeadlineTableSeeder');
        $this->call('ChapterTableSeeder');
        $this->call('WorkFunctionHasChapterTableSeeder');
        $this->call('ModulesTableSeeder');
        $this->call('OrganisationHasModuleTableSeeder');
        $this->call('CompaniesTableSeeder');
    }
}
