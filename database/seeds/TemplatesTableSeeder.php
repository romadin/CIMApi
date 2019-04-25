<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
                ['name' => 'Doel en toepassing', 'order' => 2],
                ['name' => 'Analyse', 'order' =>  3],
                ['name' => 'BIM-process', 'order' =>  4],
                ['name' => 'Informatie en data', 'order' =>  5],
                ['name' => 'Communicatie', 'order' =>  6],
                ['name' => 'Model afspraken', 'order' => 7],
                ['name' => 'Eigendom', 'order' => 10],
            ]),
            'documents' => json_encode([
                ['name' => 'Projectgegevens', 'content' => File::get(storage_path('templateText/projectData.html')), 'order' => 1],
                ['name' => 'Verplichtingen van de Opdrachtgever', 'content' => File::get(storage_path('templateText/obligationsClient.html')), 'order' => 8],
                ['name' => 'Verplichtingen van de Opdrachtnemer', 'content' => File::get(storage_path('templateText/obligationsContractor.html')), 'order' => 9],
            ]),
            'subDocuments' => json_encode([
                'Doel en toepassing' => [
                    ['name' => 'BIM-doelen', 'order' => 1, 'content' => File::get(storage_path('templateText/bimGoals.html'))],
                    ['name' => 'BIM-toepassing', 'order' => 2, 'content' => File::get(storage_path('templateText/bimUsage.html'))],
                    ['name' => 'Aspectmodellen', 'order' => 3, 'content' => File::get(storage_path('templateText/aspectModels.html'))],
                    ['name' => 'Verantwoordelijkheden', 'order' => 4, 'content' => File::get(storage_path('templateText/responsibilities.html'))],
                ],
                'Analyse' => [
                    ['name' => 'Controle output project partners', 'order' => 1, 'content' => File::get(storage_path('templateText/controlOutputProjectPartners.html'))],
                    ['name' => 'Bouwbesluit toets', 'order' => 2, 'content' => File::get(storage_path('templateText/buildDecision.html'))],
                    ['name' => 'Hoeveelheden extractie', 'order' => 3, 'content' => File::get(storage_path('templateText/loadExtraction.html'))],
                    ['name' => 'Clash detectie', 'order' => 4, 'content' => File::get(storage_path('templateText/clashDetection.html'))],
                ],
                'BIM-process' => [
                    ['name' => 'Organisatie van de (BIM-)samenwerking', 'order' => 1, 'content' => ''],
                    ['name' => 'Organisatieschema voor het project', 'order' => 2, 'content' => File::get(storage_path('templateText/organisationSchemeForProject.html'))],
                    ['name' => 'Overall workflow / proces schema', 'order' => 3, 'content' => File::get(storage_path('templateText/OverallWorkflowProcessSchema.html'))],
                ],
                'Informatie en data' => [
                    ['name' => 'Dataoverdrachtschema', 'order' => 1, 'content' => File::get(storage_path('templateText/dataTransferSchedule.html'))],
                    ['name' => 'Beheer van BIM-extracten', 'order' => 2, 'content' => File::get(storage_path('templateText/managementOfBIMExtracts.html'))],
                    ['name' => 'Uitwisselingsformaten', 'order' => 3, 'content' => File::get(storage_path('templateText/exchangeFormats.html'))],
                    ['name' => 'Modelcontrole / borging modelkwaliteit', 'order' => 4, 'content' => File::get(storage_path('templateText/modelControl.html'))],
                    ['name' => 'Droogzwemmen', 'order' => 5, 'content' => File::get(storage_path('templateText/drySwimming.html'))],
                ],
                'Communicatie' => [
                    ['name' => 'Informatie-uitwisseling', 'order' => 1, 'content' => File::get(storage_path('templateText/informationExchange.html'))],
                    ['name' => 'Gegevens behoefte schema – LEAN', 'order' => 2, 'content' => File::get(storage_path('templateText/dataRequirementSchedule.html'))],
                    ['name' => 'Clash-schema', 'order' => 3, 'content' => File::get(storage_path('templateText/clashSchedule.html'))],
                    ['name' => 'Doc. Man.Sys. (DMS) / borging modelkwaliteit', 'order' => 4, 'content' => File::get(storage_path('templateText/dmsModelAssurance.html'))],
                    ['name' => 'Communicatie van issues', 'order' => 5, 'content' => ''],
                    ['name' => '2D extracten', 'order' => 6, 'content' => ''],
                    ['name' => '3D bestanden', 'order' => 7, 'content' => ''],
                ],
                'Model afspraken' => [
                    ['name' => 'Algemene modelleerafspraken', 'order' => 1, 'content' => File::get(storage_path('templateText/generalModelingAgreements.html'))],
                    ['name' => 'Bestandnamen', 'order' => 2, 'content' => File::get(storage_path('templateText/fileNames.html'))],
                    ['name' => 'NUL-punt', 'order' => 3, 'content' => File::get(storage_path('templateText/zeroPoint.html'))],
                    ['name' => 'Bouwlaagindeling en -naamgeving', 'order' => 4, 'content' => File::get(storage_path('templateText/constructionLayerLayout.html'))],
                    ['name' => 'Modelleren van samengestelde objecten', 'order' => 13, 'content' => File::get(storage_path('templateText/modelingCompoundObjects.html'))],
                    ['name' => 'Nauwkeurigheid en toleranties', 'order' => 14, 'content' => File::get(storage_path('templateText/accuracyAndTolerances.html'))],
                ],
                'Eigendom' => [
                    ['name' => 'Intellectuele eigendom', 'order' => 9, 'content' => File::get(storage_path('templateText/intellectualOwnership.html'))],
                    ['name' => 'Eigendom van het BIM', 'order' => 10, 'content' => File::get(storage_path('templateText/propertyOfBIM.html'))],
                    ['name' => 'Aansprakelijkheid voor BIM-data', 'order' => 11, 'content' => File::get(storage_path('templateText/liabilityForBIMData.html'))],
                ],
            ]),
        ]);
    }
}
