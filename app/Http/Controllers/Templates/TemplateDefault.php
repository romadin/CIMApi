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
        ['name' => 'BIM-Uitvoeringsplan', 'order' => 1, 'isMainFunction' => true],
        ['name' => 'BIM-Modelleur', 'order' => 2],
        ['name' => 'BIM-Coördinator', 'order' => 3],
        ['name' => 'BIM Regisseur', 'order' => 4],
        ['name' => 'BIM Manager', 'order' => 5]
    ];
    const WORK_FUNCTION_HAS_HEADLINE_ORDER_DEFAULT = [2, 3, 4, 5, 6, 7, 10];
    const CHAPTERS_DEFAULT = [
        ['name' => 'Doel en toepassing', 'content' => ''],
        ['name' => 'Analyse', 'content' => ''],
        ['name' => 'BIM-process', 'content' => ''],
        ['name' => 'Informatie en data', 'content' => ''],
        ['name' => 'Communicatie', 'content' => ''],
        ['name' => 'Model afspraken', 'content' => ''],
        ['name' => 'Eigendom', 'content' => ''],
        ['name' => 'Projectgegevens', 'content' => ''],
        ['name' => 'Verplichtingen van de Opdrachtgever', 'content' => ''],
        ['name' => 'Verplichtingen van de Opdrachtnemer', 'content' => ''],
    ];
    const WORK_FUNCTION_HAS_CHAPTER = [
        ['workFunctionId' => 1, 'chapterId' => 1, 'order' => 1],
        ['workFunctionId' => 1, 'chapterId' => 2, 'order' => 2],
        ['workFunctionId' => 1, 'chapterId' => 3, 'order' => 3],
        ['workFunctionId' => 1, 'chapterId' => 4, 'order' => 4],
        ['workFunctionId' => 1, 'chapterId' => 5, 'order' => 5],
        ['workFunctionId' => 1, 'chapterId' => 6, 'order' => 6],
        ['workFunctionId' => 1, 'chapterId' => 7, 'order' => 7],
        ['workFunctionId' => 1, 'chapterId' => 8, 'order' => 8],
        ['workFunctionId' => 1, 'chapterId' => 9, 'order' => 9],
    ];
    const WORK_FUNCTION_HAS_CHAPTER_ORDER_DEFAULT = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    const SUB_CHAPTERS = [
        'Doel en toepassing' => [
            ['name' => 'BIM-doelen', 'order' => 1, 'content' => ''],
            ['name' => 'BIM-toepassing', 'order' => 2, 'content' => ''],
            ['name' => 'Aspectmodellen', 'order' => 3, 'content' => ''],
            ['name' => 'Verantwoordelijkheden', 'order' => 4, 'content' => ''],
        ],
        'Analyse' => [
            ['name' => 'Controle output project partners', 'order' => 1, 'content' => ''],
            ['name' => 'Bouwbesluit toets', 'order' => 2, 'content' => ''],
            ['name' => 'Hoeveelheden extractie', 'order' => 3, 'content' => ''],
            ['name' => 'Clash detectie', 'order' => 4, 'content' => ''],
        ],
        'BIM-process' => [
            ['name' => 'Organisatie van de (BIM-)samenwerking', 'order' => 1, 'content' => ''],
            ['name' => 'Organisatieschema voor het project', 'order' => 2, 'content' => ''],
            ['name' => 'Overall workflow / proces schema', 'order' => 3, 'content' => ''],
        ],
        'Informatie en data' => [
            ['name' => 'Dataoverdrachtschema', 'order' => 1, 'content' => ''],
            ['name' => 'Beheer van BIM-extracten', 'order' => 2, 'content' => ''],
            ['name' => 'Uitwisselingsformaten', 'order' => 3, 'content' => ''],
            ['name' => 'Modelcontrole / borging modelkwaliteit', 'order' => 4, 'content' => ''],
            ['name' => 'Droogzwemmen', 'order' => 5, 'content' => ''],
        ],
        'Communicatie' => [
            ['name' => 'Informatie-uitwisseling', 'order' => 1, 'content' => ''],
            ['name' => 'Gegevens behoefte schema – LEAN', 'order' => 2, 'content' => ''],
            ['name' => 'Clash-schema', 'order' => 3, 'content' => ''],
            ['name' => 'Doc. Man.Sys. (DMS) / borging modelkwaliteit', 'order' => 4, 'content' => ''],
            ['name' => 'Communicatie van issues', 'order' => 5, 'content' => ''],
            ['name' => '2D extracten', 'order' => 6, 'content' => ''],
            ['name' => '3D bestanden', 'order' => 7, 'content' => ''],
        ],
        'Model afspraken' => [
            ['name' => 'Algemene modelleerafspraken', 'order' => 1, 'content' => ''],
            ['name' => 'Bestandnamen', 'order' => 2, 'content' => ''],
            ['name' => 'NUL-punt', 'order' => 3, 'content' => ''],
            ['name' => 'Bouwlaagindeling en -naamgeving', 'order' => 4, 'content' => ''],
            ['name' => 'Modelleren van samengestelde objecten', 'order' => 5, 'content' => ''],
            ['name' => 'Nauwkeurigheid en toleranties', 'order' => 6, 'content' => ''],
        ],
        'Eigendom' => [
            ['name' => 'Intellectuele eigendom', 'order' => 1, 'content' => ''],
            ['name' => 'Eigendom van het BIM', 'order' => 2, 'content' => ''],
            ['name' => 'Aansprakelijkheid voor BIM-data', 'order' => 3, 'content' => ''],
        ]
    ];
}