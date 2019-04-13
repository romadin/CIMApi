<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 24-1-2019
 * Time: 00:51
 */

namespace App\Http\Handlers;


use App\Models\Folder\Folder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FoldersHandler
{
    const FOLDERS_TABLE = 'folders';
    const PROJECT_TABLE = 'projects';
    const FOLDERS_LINK_TABLE = 'folders_has_folders';

    //@todo need a better way for templating
    const defaultSubFolderTemplate = [
        ['name' => 'Doel en toepassing', 'order' => 2, 'fromTemplate' => true],
//        ['name' => 'Analyse', 'order' =>  3, 'fromTemplate' => true],
        ['name' => 'BIM-process', 'order' =>  3, 'fromTemplate' => true],
        ['name' => 'Informatie en data', 'order' =>  4, 'fromTemplate' => true],
        ['name' => 'Communicatie', 'order' =>  5, 'fromTemplate' => true],
        ['name' => 'Model afspraken', 'order' => 6, 'fromTemplate' => true],
//        ['name' => 'Planning', 'order' => 8, 'fromTemplate' => true],
//        ['name' => 'Eigendom', 'order' => 9, 'fromTemplate' => true],
//        ['name' => 'Over BIM', 'order' => 10, 'fromTemplate' => true],
    ];

    const defaultSubFolderDocumentTemplate = [
        'Doel en toepassing' => [
            ['name' => 'BIM-doelen', 'folderName' => 'bimGoals', 'order' => 1, 'fromTemplate' => true],
            ['name' => 'BIM-toepassing', 'folderName' => 'bimUsage', 'order' => 2, 'fromTemplate' => true],
            ['name' => 'Analyses', 'folderName' => 'analysis', 'order' => 3, 'fromTemplate' => true],
            ['name' => 'Aspectmodellen', 'folderName' => 'aspectModels', 'order' => 4, 'fromTemplate' => true],
            ['name' => 'Verantwoordelijkheden', 'folderName' => 'responsibilities', 'order' => 5, 'fromTemplate' => true],
        ],
//        'Analyse' => [
//            ['name' => 'Controle output project partners', 'order' => 1, 'fromTemplate' => true],
//            ['name' => 'Bouwbesluit toets', 'order' => 2, 'fromTemplate' => true],
//            ['name' => 'Hoeveelheden extractie', 'order' => 3, 'fromTemplate' => true],
//            ['name' => 'Clash detectie', 'order' => 4, 'fromTemplate' => true],
//        ],
        'BIM-process' => [
            ['name' => 'Organisatie van de (BIM-)samenwerking', 'folderName'=> 'organisationCollaboration', 'order' => 1, 'fromTemplate' => true],
            ['name' => 'Organisatieschema voor het project', 'folderName'=> 'organisationSchemeForProject', 'order' => 2, 'fromTemplate' => true],
            ['name' => 'Overall workflow / proces schema', 'folderName'=> 'OverallWorkflowProcessSchema', 'order' => 3, 'fromTemplate' => true],
        ],
        'Informatie en data' => [
            ['name' => 'Dataoverdrachtschema', 'order' => 1, 'fromTemplate' => true, 'folderName' => 'dataTransferSchedule'],
            ['name' => 'Beheer van BIM-extracten', 'order' => 2, 'fromTemplate' => true, 'folderName' => 'managementOfBIMExtracts'],
            ['name' => 'Uitwisselingsformaten', 'order' => 3, 'fromTemplate' => true, 'folderName' => 'exchangeFormats'],
            ['name' => 'Modelcontrole / borging modelkwaliteit', 'order' => 4, 'fromTemplate' => true, 'folderName' => 'modelControl'],
            ['name' => 'Droogzwemmen', 'order' => 5, 'fromTemplate' => true, 'folderName' => 'drySwimming'],
        ],
        'Communicatie' => [
            ['name' => 'Informatie-uitwisseling', 'order' => 1, 'fromTemplate' => true, 'folderName' => 'informationExchange'],
            ['name' => 'Gegevens behoefte schema – LEAN', 'order' => 2, 'fromTemplate' => true, 'folderName' => 'dataRequirementSchedule'],
            ['name' => 'Clash-schema', 'order' => 3, 'fromTemplate' => true, 'folderName' => 'clashSchedule'],
            ['name' => 'Doc. Man.Sys. (DMS) / borging modelkwaliteit', 'order' => 4, 'fromTemplate' => true, 'folderName' => 'dmsModelAssurance'],
            ['name' => 'Communicatie van issues', 'order' => 5, 'fromTemplate' => true, 'folderName' => 'communicationOfIssues'],
            ['name' => '2D extracten', 'order' => 6, 'fromTemplate' => true, 'folderName' => '2DExtraction'],
            ['name' => '3D bestanden', 'order' => 7, 'fromTemplate' => true, 'folderName' => '3DDocuments'],
        ],
        'Model afspraken' => [
            ['name' => 'Algemene modelleerafspraken', 'order' => 1, 'fromTemplate' => true, 'folderName' => 'generalModelingAgreements'],
            ['name' => 'Bestandnamen', 'order' => 2, 'fromTemplate' => true, 'folderName' => 'fileNames'],
            ['name' => 'NUL-punt', 'order' => 3, 'fromTemplate' => true, 'folderName' => 'zeroPoint'],
            ['name' => 'Bouwlaagindeling en -naamgeving', 'order' => 4, 'fromTemplate' => true, 'folderName' => 'constructionLayerLayout'],
            ['name' => 'Correct gebruik van entiteiten ', 'order' => 5, 'fromTemplate' => true, 'folderName' => 'correctUseEntities'],
            ['name' => 'Structuur en naamgeving ', 'order' => 6, 'fromTemplate' => true, 'folderName' => 'structureNaming'],
            ['name' => 'Informatie indeling classificatie NL/SfB  ', 'order' => 7, 'fromTemplate' => true, 'folderName' => 'classificationNLSfB'],
            ['name' => 'Objecten voorzien van correct materiaal', 'order' => 8, 'fromTemplate' => true, 'folderName' => 'correctMaterial'],
            ['name' => 'Doublures & doorsnijdingen', 'order' => 9, 'fromTemplate' => true, 'folderName' => 'duplicationsCuts'],
            ['name' => 'Gebruik standaard IFC Properties en Propertysets (Pset##Common)', 'order' => 10, 'fromTemplate' => true, 'folderName' => 'useDefaultIFC'],
            ['name' => 'Project specifieke propertysets', 'order' => 11, 'fromTemplate' => true, 'folderName' => 'projectSpecificPropertySets'],
            ['name' => 'Model Demarcatielijsten', 'order' => 12, 'fromTemplate' => true, 'folderName' => 'modelDemarcationLists'],
            ['name' => 'Modelleren van samengestelde objecten', 'order' => 13, 'fromTemplate' => true, 'folderName' => 'modelingCompoundObjects'],
            ['name' => 'Nauwkeurigheid en toleranties', 'order' => 14, 'fromTemplate' => true, 'folderName' => 'accuracyAndTolerances'],
        ],
//        'Planning' => [
//            ['name' => 'LEAN', 'order' => 1, 'fromTemplate' => true, 'folderName' => 'lean'],
//            ['name' => 'Projectplanning', 'order' => 2, 'fromTemplate' => true, 'folderName' => 'projectPlanning'],
//        ],
//        'Eigendom' => [
//            ['name' => 'Intellectuele eigendom ', 'order' => 1, 'fromTemplate' => true, 'folderName' => 'intellectualOwnership'],
//            ['name' => 'Eigendom van het BIM', 'order' => 2, 'fromTemplate' => true, 'folderName' => 'propertyOfBIM'],
//            ['name' => 'Aansprakelijkheid voor BIM-data', 'order' => 3, 'fromTemplate' => true, 'folderName' => 'liabilityForBIMData'],
//        ],
//        'Over BIM' => [
//            ['name' => 'Wat is BIM', 'order' => 1, 'fromTemplate' => true, 'folderName' => 'whatIsBIM'],
//            ['name' => 'Little BIM en big BIM', 'order' => 3, 'fromTemplate' => true, 'folderName' => 'littleBIMBigBIM'],
//            ['name' => 'IFC', 'order' => 4, 'fromTemplate' => true, 'folderName' => 'IFC'],
//            ['name' => 'BIR kenniskaarten', 'order' => 5, 'fromTemplate' => true, 'folderName' => 'birKnowledgeCard'],
//            ['name' => 'CB-NL', 'order' => 6, 'fromTemplate' => true, 'folderName' => 'cbNL'],
//            ['name' => 'Algemene voordelen', 'order' => 7, 'fromTemplate' => true, 'folderName' => 'generalBenefits'],
//            ['name' => 'Video', 'order' => 8, 'fromTemplate' => true, 'folderName' => 'video'],
//            ['name' => 'Definities', 'order' => 9, 'fromTemplate' => true, 'folderName' => 'Definitions'],
//        ],

    ];

    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;

    private $folderCache = [];

    public function __construct(DocumentsHandler $documentsHandler)
    {
        $this->documentsHandler = $documentsHandler;
    }

    public function getFoldersByProjectId($projectId)
    {
        try {
            $result = DB::table(self::FOLDERS_TABLE)
                ->where('projectId', $projectId)
                ->get();
            if ( $result === null) {
                return response('The project does not have folders', 400);
            }
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        $folders = [];

        foreach ($result as $folder) {
            array_push($folders, $this->makeFolder($folder));
        }

        return $folders;
    }

    public function getFolderById(int $id)
    {
        try {
            $folder = DB::table(self::FOLDERS_TABLE)
                ->where('id', $id)
                ->first();
            if ( $folder === null) {
                return response('The folder does not exist', 400);
            }
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return $this->makeFolder($folder);
    }

    public function getFolderByIdWithOrder(int $id, int $parentId)
    {
        try {
            $folder = DB::table(self::FOLDERS_TABLE)
                ->select([
                    self::FOLDERS_TABLE. '.id',
                    self::FOLDERS_TABLE. '.name',
                    self::FOLDERS_TABLE. '.projectId',
                    self::FOLDERS_TABLE. '.on',
                    self::FOLDERS_TABLE. '.mainFolder',
                    self::FOLDERS_LINK_TABLE. '.order',
                    self::FOLDERS_TABLE. '.fromTemplate',
                    self::FOLDERS_LINK_TABLE. '.folderId AS parentFolderId'])
                ->join(self::FOLDERS_LINK_TABLE, self::FOLDERS_TABLE . '.id' , '=', self::FOLDERS_LINK_TABLE . '.folderSubId')
                ->where(self::FOLDERS_LINK_TABLE.'.folderId', $parentId)
                ->where(self::FOLDERS_LINK_TABLE.'.folderSubId', $id)
                ->first();
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return $this->makeFolder($folder);
    }

    public function createFoldersTemplate(array $template, $projectId = null, $parentFolderId = null): void
    {
        foreach ($template as $folderTemplate) {
            $row = [
                'name' => $folderTemplate['name'],
                'projectId' => $projectId,
                'mainFolder' => $folderTemplate['name']=== 'BIM-Uitvoeringsplan' ? true : false,
                'fromTemplate' => $folderTemplate['fromTemplate'],
            ];
            $newFolderId = DB::table(self::FOLDERS_TABLE)->insertGetId($row);
            if ($projectId === null) {
                // if project id is null then its a link between folders, so folder gets a sub folder.
                $this->insertLink($parentFolderId, $newFolderId, $folderTemplate['order'], self::FOLDERS_LINK_TABLE,  'folderSubId');
                // on the subFolder we need to attach documents with the given template
                $this->documentsHandler->createDocumentsWithTemplate($newFolderId, self::defaultSubFolderDocumentTemplate[$folderTemplate['name']]);
            }
        }

        // If there is a parent folder id we dont want to set sub folders.
        if ( $parentFolderId === null ) {
            // @todo make a more variable sub folder template, now its hardcoded.
            $this->setSubFolderFromProjectId($projectId, self::defaultSubFolderTemplate);
        }
    }

    public function postFolder($data)
    {
        try {
            $id = DB::table(self::FOLDERS_TABLE)
                ->insertGetId([
                    'name' => $data['name'],
                    'projectId' => isset($data['projectId']) ? $data['projectId'] : null
                ]);

            if ($data['parentFolderId']) {
                DB::table(self::FOLDERS_LINK_TABLE)
                    ->insert([
                        'folderId' => $data['parentFolderId'],
                        'folderSubId' => $id,
                        'order' => $this->documentsHandler->getLatestOrderFromFolder($data['parentFolderId']) + 1
                    ]);
                return $this->getFolderByIdWithOrder($id, $data['parentFolderId']);
            }
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return $this->getFolderById($id);
    }

    public function editFolder($data, int $id): Folder
    {
        try {
            DB::table(self::FOLDERS_TABLE)
                ->where('id', $id)
                ->update(['on' => $data['turnOn']]);
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return $this->getFolderById($id);
    }

    /**
     * @param Folder | Response $folder
     * @return Response|Folder
     */
    public function deleteFolder($folder)
    {
        if( $folder instanceof Folder ) {
            if( $folder->getSubFolders() !== null ) {
                // Delete the subFolders and the linked documents
                foreach ($folder->getSubFolders() as $subFolder) {
                    $this->deleteFolder($subFolder);
                }
            }
            try {
                $this->documentsHandler->deleteDocumentsByFolderId($folder->getId());

                DB::table(self::FOLDERS_LINK_TABLE)
                    ->where('folderId', $folder->getId())
                    ->orWhere('folderSubId', $folder->getId())
                    ->delete();
                DB::table(self::FOLDERS_TABLE)->delete($folder->getId());
            }catch (\Exception $e) {
                return response('FoldersHandler: There is something wrong with the database connection', 403);
            }
        }
        return $folder;
    }

    /**
     * @param Response | Folder[] $folders
     * @return Response | Folder[]
     */
    public function deleteFolders($folders)
    {
        if ($folders instanceof Response) {
            return $folders;
        }

        try {
            foreach ($folders as $folder) {
                $this->deleteFolder($folder);
            }
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return $folders;
    }

    /**
     * Delete the subFolder link from the main folder.
     * @param $folderId
     * @param $subFolderId
     * @return int
     */
    public function deleteSubFolderLink($folderId, $subFolderId): int
    {
        try {
            DB::table(self::FOLDERS_LINK_TABLE)
                ->where('folderId', $folderId)
                ->where('folderSubId', $subFolderId)
                ->delete();
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }
        return $folderId;
    }

    /**
     * Set Sub Folders at the main folder by the given template.
     * @param int $projectId
     * @param array $template
     * @return bool | \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function setSubFolderFromProjectId(int $projectId, array $template)
    {
        try {
            $result = DB::table(self::FOLDERS_TABLE)
                ->where('projectId', $projectId)
                ->where('mainFolder', true)
                ->first();
        } catch (\Exception $e)
        {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        $this->createFoldersTemplate($template, null, $result->id);
        $this->documentsHandler->createDocumentsWithTemplate($result->id, 'default' );
        return true;
    }


    /**
     * Add an sub folder to an folder.
     * @param int $folderId
     * @param int[] $subFolderIds
     * @param int | null $order
     * @return int|Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function setLinkFolderHasSubFolder(int $folderId, $subFolderIds, $order = null)
    {
        foreach ($subFolderIds as $subFolderId) {
            $order = $order !== null ? $order : 0;
            $inserted = $this->insertLink($folderId, $subFolderId, $order, self::FOLDERS_LINK_TABLE, 'folderSubId');

            if ( !$inserted ) {
                return $inserted;
            }
        }

        return $folderId;
    }

    public function insertLink(int $folderId, int $subItemId, int $order, string $table, string $subItemColumn)
    {
        try {
            $result = DB::table($table)
                ->where('folderId', $folderId)
                ->where($subItemColumn, $subItemId)->first();
            if ( $result === null ) {
                DB::table($table)->insert([
                    'folderId' => $folderId,
                    $subItemColumn => $subItemId,
                    'order' => $order
                ]);
            } else {
                return response('The link already exist', 403);
            }
        } catch (\Exception $e) {
            return response('FoldersLinkDocumentsHandler: There is something wrong with the database connection', 500);
        }
        return true;
    }

    private function makeFolder($data): Folder
    {
        if (isset($this->folderCache[$data->id])) {
            return $this->folderCache[$data->id];
        }
        $folder = new Folder(
            $data->id,
            $data->name,
            $data->on,
            $data->mainFolder,
            isset($data->order) ? $data->order : 0,
            $data->fromTemplate,
            $data->projectId
        );

        $folder->setSubFolders($this->getSubFolders($folder, 'folderId'));

//        $folder->setParentFolders($this->getSubOrParentFolders($folder, 'folderSubId'));
        $folder->setParentFoldersId($this->getParentFoldersId($folder));

        $this->folderCache[$folder->getId()] = $folder;

        return $folder;
    }

    /**
     * @param Folder $folder
     * @param string $type = folderId | folderSubId
     * @param null | Folder $folderToDoSomething
     * @return array
     */
    private function getSubFolders(Folder $folder, string $type) {
        $subFolders = [];
        $joinOn = $type === 'folderId' ? 'folderSubId' : 'folderId';
        $subFoldersResult = DB::table(self::FOLDERS_LINK_TABLE)
            ->select([
                self::FOLDERS_TABLE. '.id',
                self::FOLDERS_TABLE. '.name',
                self::FOLDERS_TABLE. '.projectId',
                self::FOLDERS_TABLE. '.on',
                self::FOLDERS_TABLE. '.mainFolder',
                self::FOLDERS_LINK_TABLE. '.order',
                self::FOLDERS_TABLE. '.fromTemplate',
                self::FOLDERS_LINK_TABLE. '.folderId AS parentFolderId'])
                ->join(self::FOLDERS_TABLE, self::FOLDERS_LINK_TABLE . '.' . $joinOn , '=', self::FOLDERS_TABLE . '.id')
                ->where(self::FOLDERS_LINK_TABLE. '.' . $type, '=', $folder->getId() )
                ->get();

        if (! empty($subFoldersResult)) {
            foreach ($subFoldersResult as $result) {
                array_push($subFolders, $this->makeFolder($result, $folder));
            }
        }
        return $subFolders;
    }

    private function getParentFoldersId(Folder $folder) {

        $parentFoldersId = [];
        $parentFolders = DB::table(self::FOLDERS_LINK_TABLE)
            ->select([self::FOLDERS_LINK_TABLE.'.folderId'])
            ->join(self::FOLDERS_TABLE, self::FOLDERS_LINK_TABLE . '.folderId' , '=', self::FOLDERS_TABLE . '.id')
            ->where(self::FOLDERS_LINK_TABLE. '.folderSubId', '=', $folder->getId() )
            ->get();

        if (! empty($parentFolders)) {
            foreach ($parentFolders as $result) {
                array_push($parentFoldersId, $result->folderId);
            }
        }
        return $parentFoldersId;
    }

}