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
    const CHAPTERS_DEFAULT = [
        ['name' => 'Projectgegevens', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'Doel en toepassing', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'Analyse', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'BIM-process', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'Informatie en data', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'Communicatie', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'Model afspraken', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'Eigendom', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'Verplichtingen van de Opdrachtgever', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
        ['name' => 'Verplichtingen van de Opdrachtnemer', 'content' => '', 'parentChapterId' => NULL, 'order' => NULL],
    ];
    const WORK_FUNCTION_HAS_HEADLINE_ORDER_DEFAULT = [2, 3, 4, 5, 6, 7, 10];
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
        ['workFunctionId' => 1, 'chapterId' => 10, 'order' => 10],
    ];
    const WORK_FUNCTION_HAS_CHAPTER_ORDER_DEFAULT = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    const SUB_CHAPTERS = [
        'Doel en toepassing' => [
            ['name' => 'BIM-doelen', 'order' => 1, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'BIM-toepassing', 'order' => 2, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Aspectmodellen', 'order' => 3, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Verantwoordelijkheden', 'order' => 4, 'content' => '', 'parentChapterId' => NULL],
        ],
        'Analyse' => [
            ['name' => 'Controle output project partners', 'order' => 1, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Bouwbesluit toets', 'order' => 2, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Hoeveelheden extractie', 'order' => 3, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Clash detectie', 'order' => 4, 'content' => '', 'parentChapterId' => NULL],
        ],
        'BIM-process' => [
            ['name' => 'Organisatie van de (BIM-)samenwerking', 'order' => 1, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Organisatieschema voor het project', 'order' => 2, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Overall workflow / proces schema', 'order' => 3, 'content' => '', 'parentChapterId' => NULL],
        ],
        'Informatie en data' => [
            ['name' => 'Dataoverdrachtschema', 'order' => 1, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Beheer van BIM-extracten', 'order' => 2, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Uitwisselingsformaten', 'order' => 3, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Modelcontrole / borging modelkwaliteit', 'order' => 4, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Droogzwemmen', 'order' => 5, 'content' => '', 'parentChapterId' => NULL],
        ],
        'Communicatie' => [
            ['name' => 'Informatie-uitwisseling', 'order' => 1, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Gegevens behoefte schema – LEAN', 'order' => 2, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Clash-schema', 'order' => 3, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Doc. Man.Sys. (DMS) / borging modelkwaliteit', 'order' => 4, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Communicatie van issues', 'order' => 5, 'content' => '', 'parentChapterId' => NULL],
            ['name' => '2D extracten', 'order' => 6, 'content' => '', 'parentChapterId' => NULL],
            ['name' => '3D bestanden', 'order' => 7, 'content' => '', 'parentChapterId' => NULL],
        ],
        'Model afspraken' => [
            ['name' => 'Algemene modelleerafspraken', 'order' => 1, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Bestandnamen', 'order' => 2, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'NUL-punt', 'order' => 3, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Bouwlaagindeling en -naamgeving', 'order' => 4, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Modelleren van samengestelde objecten', 'order' => 5, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Nauwkeurigheid en toleranties', 'order' => 6, 'content' => '', 'parentChapterId' => NULL],
        ],
        'Eigendom' => [
            ['name' => 'Intellectuele eigendom', 'order' => 1, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Eigendom van het BIM', 'order' => 2, 'content' => '', 'parentChapterId' => NULL],
            ['name' => 'Aansprakelijkheid voor BIM-data', 'order' => 3, 'content' => '', 'parentChapterId' => NULL],
        ]
    ];
}