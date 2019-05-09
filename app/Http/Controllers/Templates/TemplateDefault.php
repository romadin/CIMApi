<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 30-4-2019
 * Time: 15:05
 */

namespace App\Http\Controllers\Templates;


class TemplateDefault
{
    const WORK_FUNCTIONS = [
        ['name' => 'BIM-Uitvoeringsplan', 'order' => 0],
        ['name' => 'BIM-Modelleur', 'order' => 0],
        ['name' => 'BIM-Coördinator', 'order' => 0],
        ['name' => 'BIM Regisseur', 'order' => 0],
        ['name' => 'BIM Manager', 'order' => 0]
    ];
    const HEADLINES_DEFAULT = [
        ['name' => 'Doel en toepassing'],
        ['name' => 'Analyse'],
        ['name' => 'BIM-process'],
        ['name' => 'Informatie en data'],
        ['name' => 'Communicatie'],
        ['name' => 'Model afspraken'],
        ['name' => 'Eigendom'],
    ];
    const WORK_FUNCTION_HAS_HEADLINE = [
        ['workFunctionId' => 1, 'headlineId' => 1, 'order' => 2],
        ['workFunctionId' => 1, 'headlineId' => 2, 'order' => 3],
        ['workFunctionId' => 1, 'headlineId' => 3, 'order' => 4],
        ['workFunctionId' => 1, 'headlineId' => 4, 'order' => 5],
        ['workFunctionId' => 1, 'headlineId' => 5, 'order' => 6],
        ['workFunctionId' => 1, 'headlineId' => 6, 'order' => 7],
        ['workFunctionId' => 1, 'headlineId' => 7, 'order' => 10],
    ];
    const CHAPTERS_DEFAULT = [
        ['name' => 'Projectgegevens', 'content' => ''],
        ['name' => 'Verplichtingen van de Opdrachtgever', 'content' => ''],
        ['name' => 'Verplichtingen van de Opdrachtnemer', 'content' => ''],
    ];
    const WORK_FUNCTION_HAS_CHAPTER = [
        ['workFunctionId' => 1, 'chapterId' => 1, 'order' => 1],
        ['workFunctionId' => 1, 'chapterId' => 2, 'order' => 8],
        ['workFunctionId' => 1, 'chapterId' => 3, 'order' => 9],
    ];
    const SUB_DOCUMENTS_DEFAULT = [
        [
            'name' => 'Doel en toepassing',
            'items' => [
                ['name' => 'BIM-doelen', 'order' => 1, 'content' => ''],
                ['name' => 'BIM-toepassing', 'order' => 2, 'content' => ''],
                ['name' => 'Aspectmodellen', 'order' => 3, 'content' => ''],
                ['name' => 'Verantwoordelijkheden', 'order' => 4, 'content' => ''],
            ]
        ],
        [
            'name' => 'Analyse',
            'items' =>  [
                ['name' => 'Controle output project partners', 'order' => 1, 'content' => ''],
                ['name' => 'Bouwbesluit toets', 'order' => 2, 'content' => ''],
                ['name' => 'Hoeveelheden extractie', 'order' => 3, 'content' => ''],
                ['name' => 'Clash detectie', 'order' => 4, 'content' => ''],
            ]
        ],
        [
            'name' => 'BIM-process',
            'items' => [
                ['name' => 'Organisatie van de (BIM-)samenwerking', 'order' => 1, 'content' => ''],
                ['name' => 'Organisatieschema voor het project', 'order' => 2, 'content' => ''],
                ['name' => 'Overall workflow / proces schema', 'order' => 3, 'content' => ''],
            ]
        ],
        [
            'name' => 'Informatie en data',
            'items' => [
                ['name' => 'Dataoverdrachtschema', 'order' => 1, 'content' => ''],
                ['name' => 'Beheer van BIM-extracten', 'order' => 2, 'content' => ''],
                ['name' => 'Uitwisselingsformaten', 'order' => 3, 'content' => ''],
                ['name' => 'Modelcontrole / borging modelkwaliteit', 'order' => 4, 'content' => ''],
                ['name' => 'Droogzwemmen', 'order' => 5, 'content' => ''],
            ]
        ],
        [
            'name' => 'Communicatie',
            'items' => [
                ['name' => 'Informatie-uitwisseling', 'order' => 1, 'content' => ''],
                ['name' => 'Gegevens behoefte schema – LEAN', 'order' => 2, 'content' => ''],
                ['name' => 'Clash-schema', 'order' => 3, 'content' => ''],
                ['name' => 'Doc. Man.Sys. (DMS) / borging modelkwaliteit', 'order' => 4, 'content' => ''],
                ['name' => 'Communicatie van issues', 'order' => 5, 'content' => ''],
                ['name' => '2D extracten', 'order' => 6, 'content' => ''],
                ['name' => '3D bestanden', 'order' => 7, 'content' => ''],
            ]
        ],
        [
            'name' => 'Model afspraken',
            'items' => [
                ['name' => 'Algemene modelleerafspraken', 'order' => 1, 'content' => ''],
                ['name' => 'Bestandnamen', 'order' => 2, 'content' => ''],
                ['name' => 'NUL-punt', 'order' => 3, 'content' => ''],
                ['name' => 'Bouwlaagindeling en -naamgeving', 'order' => 4, 'content' => ''],
                ['name' => 'Modelleren van samengestelde objecten', 'order' => 13, 'content' => ''],
                ['name' => 'Nauwkeurigheid en toleranties', 'order' => 14, 'content' => ''],
            ]
        ],
        [
            'name' => 'Eigendom',
            'items' => [
                ['name' => 'Intellectuele eigendom', 'order' => 9, 'content' => ''],
                ['name' => 'Eigendom van het BIM', 'order' => 10, 'content' => ''],
                ['name' => 'Aansprakelijkheid voor BIM-data', 'order' => 11, 'content' => ''],
            ]
        ]
    ];
}