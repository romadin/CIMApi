<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkFunctionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('work_functions')->insert([
            [
                'name' => 'BIM-Uitvoeringsplan',
                'isMainFunction' => true,
                'order' => 1,
                'templateId' => 1,
            ],
            [
                'name' => 'BIM-Modelleur',
                'isMainFunction' => false,
                'order' => 2,
                'templateId' => 1,
            ],
            [
                'name' => 'BIM-Coördinator',
                'isMainFunction' => false,
                'order' => 3,
                'templateId' => 1,
            ],
            [
                'name' => 'BIM Regisseur',
                'isMainFunction' => false,
                'order' => 4,
                'templateId' => 1,
            ],
            [
                'name' => 'BIM Manager',
                'isMainFunction' => false,
                'order' => 5,
                'templateId' => 1,
            ]
        ]);
    }
}
