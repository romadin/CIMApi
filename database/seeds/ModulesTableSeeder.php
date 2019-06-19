<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('modules')->insert([
            [
                'name' => 'Templates',
            ],
            [
                'name' => 'Assign chapter company',
            ],
            [
                'name' => 'Branding',
            ],
            [
                'name' => 'Basic ILS',
            ],
        ]);
    }
}
