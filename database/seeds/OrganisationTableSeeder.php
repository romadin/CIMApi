<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganisationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('organisations')->insert([
            [
                'id' => 0,
                'name' => 'ghost',
            ],
            [
                'id' => 1,
                'name' => 'demo',
            ]
        ]);
    }
}
