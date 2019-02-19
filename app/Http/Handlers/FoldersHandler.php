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
        ['name' => 'Model afspraak', 'order' => 7, 'fromTemplate' => true],
        ['name' => 'Analyse', 'order' =>  9, 'fromTemplate' => true],
        ['name' => 'Planning', 'order' => 10, 'fromTemplate' => true],
        ['name' => 'Informatiebehoefte', 'order' => 11, 'fromTemplate' => true],
        ['name' => 'Over BIM', 'order' => 12, 'fromTemplate' => true],
    ];

    const defaultSubFolderDocumentTemplate = [
        'Model afspraak' => [
            ['name' => 'Bestandformaten', 'order' => 1, 'fromTemplate' => true],
            ['name' => 'IFC export instelling', 'order' => 2, 'fromTemplate' => true],
            ['name' => 'Bestandnamen', 'order' => 3, 'fromTemplate' => true],
            ['name' => 'NUL-punt', 'order' => 4, 'fromTemplate' => true],
            ['name' => 'Object- en materiaalbeschrijving', 'order' => 5, 'fromTemplate' => true],
            ['name' => 'Parameters', 'order' => 6, 'fromTemplate' => true],
            ['name' => 'Ruimte objecten', 'order' => 7, 'fromTemplate' => true],
            ['name' => 'Ruimte afwerking', 'order' => 8, 'fromTemplate' => true],
            ['name' => 'Zones', 'order' => 9, 'fromTemplate' => true],
        ],
        'Analyse' => [
            ['name' => 'Constructie analyse', 'order' => 1, 'fromTemplate' => true],
            ['name' => 'Brandveiligheid', 'order' => 2, 'fromTemplate' => true],
            ['name' => 'Akoestiek', 'order' => 3, 'fromTemplate' => true],
            ['name' => 'Energieverbruik', 'order' => 4, 'fromTemplate' => true],
            ['name' => 'Kosten calculatie', 'order' => 5, 'fromTemplate' => true],
            ['name' => 'Planning', 'order' => 6, 'fromTemplate' => true],
        ],
        'Planning' => [
            ['name' => 'LEAN', 'order' => 1, 'fromTemplate' => true],
            ['name' => 'Projectplanning', 'order' => 2, 'fromTemplate' => true],
        ],
        'Informatiebehoefte' => [
            ['name' => 'Specifieke gevraagde informatie Bedr. A', 'order' => 1, 'fromTemplate' => true],
            ['name' => 'Specifieke gevraagde informatie Bedr. B', 'order' => 2, 'fromTemplate' => true],
        ],
        'Over BIM' => [
            ['name' => 'Wat is BIM', 'order' => 1, 'fromTemplate' => true],
            ['name' => 'Little BIM en big BIM', 'order' => 3, 'fromTemplate' => true],
            ['name' => 'IFC', 'order' => 4, 'fromTemplate' => true],
            ['name' => 'BIR kenniskaarten', 'order' => 5, 'fromTemplate' => true],
            ['name' => 'BIR kenniskaarten', 'order' => 6, 'fromTemplate' => true],
            ['name' => 'CB-NL', 'order' => 7, 'fromTemplate' => true],
            ['name' => 'Algemene voordelen', 'order' => 8, 'fromTemplate' => true],
        ],

    ];

    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;

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
                var_dump($e->getMessage(), $folder->getId());
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
        $subFolders = [];
        $subFoldersResult = DB::table(self::FOLDERS_LINK_TABLE)
            ->select([
                self::FOLDERS_TABLE. '.id',
                self::FOLDERS_TABLE. '.name',
                self::FOLDERS_TABLE. '.projectId',
                self::FOLDERS_TABLE. '.on',
                self::FOLDERS_TABLE. '.mainFolder',
                self::FOLDERS_LINK_TABLE. '.order',
                self::FOLDERS_TABLE. '.fromTemplate'])
            ->join(self::FOLDERS_TABLE, self::FOLDERS_LINK_TABLE . '.folderSubId', '=', self::FOLDERS_TABLE . '.id')
            ->where(self::FOLDERS_LINK_TABLE. '.folderId', '=', $data->id )
            ->get();

        if (! empty($subFoldersResult)) {
            foreach ($subFoldersResult as $result) {
                array_push($subFolders, $this->makeFolder($result));
            }
        }

        $folder = new Folder(
            $data->id,
            $data->name,
            $data->on,
            $data->mainFolder,
            isset($data->order) ? $data->order : 0,
            $data->fromTemplate,
            $data->projectId,
            empty($subFolders) ? null : $subFolders
        );

        return $folder;
    }

}