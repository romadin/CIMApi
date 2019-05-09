<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('templates')->insert([
            'name' => 'Standaard',
            'organisationId' => 0,
            'isDefault' => true,
        ]);
    }
}
