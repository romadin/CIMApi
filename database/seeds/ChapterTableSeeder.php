<?php

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
            [
                'name' => 'Projectgegevens',
                'order' => NULL,
                'content' => File::get(storage_path('templateText/projectData.html')),
                'headlineId' => NULL
            ],
            [
                'name' => 'Verplichtingen van de Opdrachtgever',
                'order' => NULL,
                'content' => File::get(storage_path('templateText/obligationsClient.html')),
                'headlineId' => NULL
            ],
            [
                'name' => 'Verplichtingen van de Opdrachtnemer',
                'order' => NULL,
                'content' => File::get(storage_path('templateText/obligationsContractor.html')),
                'headlineId' => NULL
            ],
            [
                'name' => 'BIM-doelen',
                'order' => 1,
                'content' => File::get(storage_path('templateText/bimGoals.html')),
                'headlineId' => 1
            ],
            [
                'name' => 'BIM-toepassing',
                'order' => 2,
                'content' => File::get(storage_path('templateText/bimUsage.html')),
                'headlineId' => 1
            ],
            [
                'name' => 'Aspectmodellen',
                'order' => 3,
                'content' => File::get(storage_path('templateText/aspectModels.html')),
                'headlineId' => 1
            ],
            [
                'name' => 'Verantwoordelijkheden',
                'order' => 4,
                'content' => File::get(storage_path('templateText/responsibilities.html')),
                'headlineId' => 1
            ],
            [
                'name' => 'Controle output project partners',
                'order' => 1,
                'content' => File::get(storage_path('templateText/controlOutputProjectPartners.html')),
                'headlineId' => 2
            ],
            [
                'name' => 'Bouwbesluit toets',
                'order' => 2,
                'content' => File::get(storage_path('templateText/buildDecision.html')),
                'headlineId' => 2
            ],
            [
                'name' => 'Hoeveelheden extractie',
                'order' => 3,
                'content' => File::get(storage_path('templateText/loadExtraction.html')),
                'headlineId' => 2
            ],
            [
                'name' => 'Clash detectie',
                'order' => 4,
                'content' => File::get(storage_path('templateText/clashDetection.html')),
                'headlineId' => 2
            ],
            [
                'name' => 'Organisatie van de (BIM-)samenwerking',
                'order' => 1,
                'content' => '',
                'headlineId' => 3
            ],
            [
                'name' => 'Organisatieschema voor het project',
                'order' => 2,
                'content' => File::get(storage_path('templateText/organisationSchemeForProject.html')),
                'headlineId' => 3
            ],
            [
                'name' => 'Overall workflow / proces schema',
                'order' => 3,
                'content' => File::get(storage_path('templateText/OverallWorkflowProcessSchema.html')),
                'headlineId' => 3
            ],
            [
                'name' => 'Dataoverdrachtschema',
                'order' => 1,
                'content' => File::get(storage_path('templateText/dataTransferSchedule.html')),
                'headlineId' => 4
            ],
            [
                'name' => 'Beheer van BIM-extracten',
                'order' => 2,
                'content' => File::get(storage_path('templateText/managementOfBIMExtracts.html')),
                'headlineId' => 4
            ],
            [
                'name' => 'Uitwisselingsformaten',
                'order' => 3,
                'content' => File::get(storage_path('templateText/exchangeFormats.html')),
                'headlineId' => 4
            ],
            [
                'name' => 'Modelcontrole / borging modelkwaliteit',
                'order' => 4,
                'content' => File::get(storage_path('templateText/modelControl.html')),
                'headlineId' => 4
            ],
            [
                'name' => 'Droogzwemmen',
                'order' => 5,
                'content' => File::get(storage_path('templateText/drySwimming.html')),
                'headlineId' => 4
            ],
            [
                'name' => 'Informatie-uitwisseling',
                'order' => 1,
                'content' => File::get(storage_path('templateText/informationExchange.html')),
                'headlineId' => 5
            ],
            [
                'name' => 'Gegevens behoefte schema – LEAN',
                'order' => 2,
                'content' => File::get(storage_path('templateText/dataRequirementSchedule.html')),
                'headlineId' => 5
            ],
            [
                'name' => 'Clash-schema',
                'order' => 3,
                'content' => File::get(storage_path('templateText/clashSchedule.html')),
                'headlineId' => 5
            ],
            [
                'name' => 'Doc. Man.Sys. (DMS) / borging modelkwaliteit',
                'order' => 4,
                'content' => File::get(storage_path('templateText/dmsModelAssurance.html')),
                'headlineId' => 5
            ],
            [
                'name' => 'Communicatie van issues',
                'order' => 5,
                'content' => '',
                'headlineId' => 5
            ],
            [
                'name' => '2D extracten',
                'order' => 6,
                'content' => '',
                'headlineId' => 5
            ],
            [
                'name' => '3D bestanden',
                'order' => 7,
                'content' => '',
                'headlineId' => 5
            ],
            [
                'name' => 'Algemene modelleerafspraken',
                'order' => 1,
                'content' => File::get(storage_path('templateText/generalModelingAgreements.html')),
                'headlineId' => 6
            ],
            [
                'name' => 'Bestandnamen',
                'order' => 2,
                'content' => File::get(storage_path('templateText/fileNames.html')),
                'headlineId' => 6
            ],
            [
                'name' => 'NUL-punt',
                'order' => 3,
                'content' => File::get(storage_path('templateText/zeroPoint.html')),
                'headlineId' => 6
            ],
            [
                'name' => 'Bouwlaagindeling en -naamgeving',
                'order' => 4,
                'content' => File::get(storage_path('templateText/constructionLayerLayout.html')),
                'headlineId' => 6
            ],
            [
                'name' => 'Modelleren van samengestelde objecten',
                'order' => 13,
                'content' => File::get(storage_path('templateText/modelingCompoundObjects.html')),
                'headlineId' => 6
            ],
            [
                'name' => 'Nauwkeurigheid en toleranties',
                'order' => 14,
                'content' => File::get(storage_path('templateText/accuracyAndTolerances.html')),
                'headlineId' => 6
            ],
            [
                'name' => 'Intellectuele eigendom',
                'order' => 9,
                'content' => File::get(storage_path('templateText/intellectualOwnership.html')),
                'headlineId' => 7
            ],
            [
                'name' => 'Eigendom van het BIM',
                'order' => 10,
                'content' => File::get(storage_path('templateText/propertyOfBIM.html')),
                'headlineId' => 7
            ],
            [
                'name' => 'Aansprakelijkheid voor BIM-data',
                'order' => 11,
                'content' => File::get(storage_path('templateText/liabilityForBIMData.html')),
                'headlineId' => 7
            ],
        ]);
    }
}
