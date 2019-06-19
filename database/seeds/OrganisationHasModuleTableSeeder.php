<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganisationHasModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('organisation_has_module')->insert([
            [
                'organisationId' => 2,
                'moduleId' => 1,
                'isOn' => true,
            ],
            [
                'organisationId' => 2,
                'moduleId' => 2,
                'isOn' => true,
            ],
        ]);
    }
}
