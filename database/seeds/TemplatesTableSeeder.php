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
            'name' => 'default',
            'folders' => json_encode([
                ['name' => 'BIM-Uitvoeringsplan', 'order' => 0],
                ['name' => 'BIM-Modelleur', 'order' => 0],
                ['name' => 'BIM-Coördinator', 'order' => 0],
                ['name' => 'BIM Regisseur', 'order' => 0],
                ['name' => 'BIM Manager', 'order' => 0]
            ]),
            'subFolders' => json_encode([
                ['name' => 'Doel en toepassing', 'order' => 2, 'fromTemplate' => true],
                ['name' => 'Analyse', 'order' =>  3, 'fromTemplate' => true],
                ['name' => 'BIM-process', 'order' =>  4, 'fromTemplate' => true],
                ['name' => 'Informatie en data', 'order' =>  5, 'fromTemplate' => true],
                ['name' => 'Communicatie', 'order' =>  6, 'fromTemplate' => true],
                ['name' => 'Model afspraken', 'order' => 7, 'fromTemplate' => true],
                ['name' => 'Eigendom', 'order' => 10, 'fromTemplate' => true],
            ]),
            'documents' => json_encode([
                ['name' => 'Projectgegevens', 'folderName' => 'projectData', 'order' => 1, 'fromTemplate' => true],
                ['name' => 'Verplichtingen van de Opdrachtgever', 'folderName' => 'obligationsClient', 'order' => 8, 'fromTemplate' => true],
                ['name' => 'Verplichtingen van de Opdrachtnemer', 'folderName' => 'obligationsContractor', 'order' => 9, 'fromTemplate' => true],
            ]),
            'subDocuments' => '{}',
        ]);
    }
}
