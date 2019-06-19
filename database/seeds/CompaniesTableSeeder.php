<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')->insert([
            [
                'name' => 'Default Company',
            ],
            [
                'name' => 'Sixpaths',
            ],
            [
                'name' => 'De wil ik niet terug zien',
            ],
            [
                'name' => 'Bedrijf 4',
            ],
            [
                'name' => 'Bedrijf 5',
            ],
        ]);
    }
}
