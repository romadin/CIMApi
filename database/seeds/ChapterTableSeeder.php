<?php

use App\Http\Controllers\Templates\TemplateDefault;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ChapterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('chapters')->insert([
            ['name' => 'Doel en toepassing' ,'order' => 1, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Analyse' ,'order' => 2, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'BIM-process' ,'order' => 3, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Informatie en data' ,'order' => 4, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Communicatie' ,'order' => 5, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Model afspraken' ,'order' => 6, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Eigendom' ,'order' => 7, 'content' => '', 'parentChapterId' => NULL],
            [
                'name' => 'Projectgegevens',
                'order' => 8,
                'content' => File::get(storage_path('templateText/projectData.html')),
                'parentChapterId' => NULL
            ],
            [
                'name' => 'Verplichtingen van de Opdrachtgever',
                'order' => 9,
                'content' => File::get(storage_path('templateText/obligationsClient.html')),
                'parentChapterId' => NULL
            ],
            [
                'name' => 'Verplichtingen van de Opdrachtnemer',
                'order' => 10,
                'content' => File::get(storage_path('templateText/obligationsContractor.html')),
                'parentChapterId' => NULL
            ],
            [
                'name' => 'BIM-doelen',
                'order' => 1,
                'content' => File::get(storage_path('templateText/bimGoals.html')),
                'parentChapterId' => 1
            ],
            [
                'name' => 'BIM-toepassing',
                'order' => 2,
                'content' => File::get(storage_path('templateText/bimUsage.html')),
                'parentChapterId' => 1
            ],
            [
                'name' => 'Aspectmodellen',
                'order' => 3,
                'content' => File::get(storage_path('templateText/aspectModels.html')),
                'parentChapterId' => 1
            ],
            [
                'name' => 'Verantwoordelijkheden',
                'order' => 4,
                'content' => File::get(storage_path('templateText/responsibilities.html')),
                'parentChapterId' => 1
            ],
            [
                'name' => 'Controle output project partners',
                'order' => 1,
                'content' => File::get(storage_path('templateText/controlOutputProjectPartners.html')),
                'parentChapterId' => 2
            ],
            [
                'name' => 'Bouwbesluit toets',
                'order' => 2,
                'content' => File::get(storage_path('templateText/buildDecision.html')),
                'parentChapterId' => 2
            ],
            [
                'name' => 'Hoeveelheden extractie',
                'order' => 3,
                'content' => File::get(storage_path('templateText/loadExtraction.html')),
                'parentChapterId' => 2
            ],
            [
                'name' => 'Clash detectie',
                'order' => 4,
                'content' => File::get(storage_path('templateText/clashDetection.html')),
                'parentChapterId' => 2
            ],
            [
                'name' => 'Organisatie van de (BIM-)samenwerking',
                'order' => 1,
                'content' => '',
                'parentChapterId' => 3
            ],
            [
                'name' => 'Organisatieschema voor het project',
                'order' => 2,
                'content' => File::get(storage_path('templateText/organisationSchemeForProject.html')),
                'parentChapterId' => 3
            ],
            [
                'name' => 'Overall workflow / proces schema',
                'order' => 3,
                'content' => File::get(storage_path('templateText/overallWorkflowProcessSchema.html')),
                'parentChapterId' => 3
            ],
            [
                'name' => 'Dataoverdrachtschema',
                'order' => 1,
                'content' => File::get(storage_path('templateText/dataTransferSchedule.html')),
                'parentChapterId' => 4
            ],
            [
                'name' => 'Beheer van BIM-extracten',
                'order' => 2,
                'content' => File::get(storage_path('templateText/managementOfBIMExtracts.html')),
                'parentChapterId' => 4
            ],
            [
                'name' => 'Uitwisselingsformaten',
                'order' => 3,
                'content' => File::get(storage_path('templateText/exchangeFormats.html')),
                'parentChapterId' => 4
            ],
            [
                'name' => 'Modelcontrole / borging modelkwaliteit',
                'order' => 4,
                'content' => File::get(storage_path('templateText/modelControl.html')),
                'parentChapterId' => 4
            ],
            [
                'name' => 'Droogzwemmen',
                'order' => 5,
                'content' => File::get(storage_path('templateText/drySwimming.html')),
                'parentChapterId' => 4
            ],
            [
                'name' => 'Informatie-uitwisseling',
                'order' => 1,
                'content' => File::get(storage_path('templateText/informationExchange.html')),
                'parentChapterId' => 5
            ],
            [
                'name' => 'Gegevens behoefte schema – LEAN',
                'order' => 2,
                'content' => File::get(storage_path('templateText/dataRequirementSchedule.html')),
                'parentChapterId' => 5
            ],
            [
                'name' => 'Clash-schema',
                'order' => 3,
                'content' => File::get(storage_path('templateText/clashSchedule.html')),
                'parentChapterId' => 5
            ],
            [
                'name' => 'Doc. Man.Sys. (DMS) / borging modelkwaliteit',
                'order' => 4,
                'content' => File::get(storage_path('templateText/dmsModelAssurance.html')),
                'parentChapterId' => 5
            ],
            [
                'name' => 'Communicatie van issues',
                'order' => 5,
                'content' => '',
                'parentChapterId' => 5
            ],
            [
                'name' => '2D extracten',
                'order' => 6,
                'content' => '',
                'parentChapterId' => 5
            ],
            [
                'name' => '3D bestanden',
                'order' => 7,
                'content' => '',
                'parentChapterId' => 5
            ],
            [
                'name' => 'Algemene modelleerafspraken',
                'order' => 1,
                'content' => File::get(storage_path('templateText/generalModelingAgreements.html')),
                'parentChapterId' => 6
            ],
            [
                'name' => 'Bestandnamen',
                'order' => 2,
                'content' => File::get(storage_path('templateText/fileNames.html')),
                'parentChapterId' => 6
            ],
            [
                'name' => 'NUL-punt',
                'order' => 3,
                'content' => File::get(storage_path('templateText/zeroPoint.html')),
                'parentChapterId' => 6
            ],
            [
                'name' => 'Bouwlaagindeling en -naamgeving',
                'order' => 4,
                'content' => File::get(storage_path('templateText/constructionLayerLayout.html')),
                'parentChapterId' => 6
            ],
            [
                'name' => 'Modelleren van samengestelde objecten',
                'order' => 13,
                'content' => File::get(storage_path('templateText/modelingCompoundObjects.html')),
                'parentChapterId' => 6
            ],
            [
                'name' => 'Nauwkeurigheid en toleranties',
                'order' => 14,
                'content' => File::get(storage_path('templateText/accuracyAndTolerances.html')),
                'parentChapterId' => 6
            ],
            [
                'name' => 'Intellectuele eigendom',
                'order' => 9,
                'content' => File::get(storage_path('templateText/intellectualOwnership.html')),
                'parentChapterId' => 7
            ],
            [
                'name' => 'Eigendom van het BIM',
                'order' => 10,
                'content' => File::get(storage_path('templateText/propertyOfBIM.html')),
                'parentChapterId' => 7
            ],
            [
                'name' => 'Aansprakelijkheid voor BIM-data',
                'order' => 11,
                'content' => File::get(storage_path('templateText/liabilityForBIMData.html')),
                'parentChapterId' => 7
            ],
        ]);
    }
}
