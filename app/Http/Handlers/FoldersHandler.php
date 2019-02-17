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
        ['name' => 'Model afspraak', 'order' => 7],
        ['name' => 'Analyse', 'order' =>  9],
        ['name' => 'Planning', 'order' => 10],
        ['name' => 'Informatiebehoefte', 'order' => 11],
        ['name' => 'Over BIM', 'order' => 13],
    ];

    const defaultSubFolderDocumentTemplate = [
        'Model afspraak' => [
            ['name' => 'Bestandformaten', 'order' => 1],
            ['name' => 'IFC export instelling', 'order' => 2],
            ['name' => 'Bestandnamen', 'order' => 3],
            ['name' => 'NUL-punt', 'order' => 4],
            ['name' => 'Object- en materiaalbeschrijving', 'order' => 5],
            ['name' => 'Parameters', 'order' => 6],
            ['name' => 'Ruimte objecten', 'order' => 7],
            ['name' => 'Ruimte afwerking', 'order' => 8],
            ['name' => 'Zones', 'order' => 9],
        ],
        'Analyse' => [
            ['name' => 'Constructie analyse', 'order' => 1],
            ['name' => 'Brandveiligheid', 'order' => 2],
            ['name' => 'Akoestiek', 'order' => 3],
            ['name' => 'Energieverbruik', 'order' => 4],
            ['name' => 'Kosten calculatie', 'order' => 5],
            ['name' => 'Planning', 'order' => 6],
        ],
        'Planning' => [
            ['name' => 'LEAN', 'order' => 1],
            ['name' => 'Projectplanning', 'order' => 2],
        ],
        'Informatiebehoefte' => [
            ['name' => 'Specifieke gevraagde informatie Bedr. A', 'order' => 1],
            ['name' => 'Specifieke gevraagde informatie Bedr. B', 'order' => 2],
        ],
        'Over BIM' => [
            ['name' => 'Wat is BIM', 'order' => 1],
            ['name' => 'Little BIM en big BIM', 'order' => 3],
            ['name' => 'IFC', 'order' => 4],
            ['name' => 'BIR kenniskaarten', 'order' => 5],
            ['name' => 'BIR kenniskaarten', 'order' => 6],
            ['name' => 'CB-NL', 'order' => 7],
            ['name' => 'Algemene voordelen', 'order' => 8],
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
            ];
            $newFolderId = DB::table(self::FOLDERS_TABLE)->insertGetId($row);
            if ($projectId === null) {
                // if project id is null then its a link between folders, so folder gets a sub folder.
                $this->setLinkFolderHasSubFolder( $parentFolderId, $newFolderId, $folderTemplate['order']);
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

    private function setLinkFolderHasSubFolder(int $folderId, int $subFolderId, int $order)
    {
        if ($folderId === $subFolderId) {
            return response('FoldersHandler: cant give the same id', 400);
        }
        try {
            DB::table(self::FOLDERS_LINK_TABLE)
                ->insert([
                    'folderId' => $folderId,
                    'folderSubId' => $subFolderId,
                    'order' => $order,
                ]);
        } catch (\Exception $e)
        {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }
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
                self::FOLDERS_LINK_TABLE. '.order'])
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
            $data->projectId,
            empty($subFolders) ? null : $subFolders
        );

        return $folder;
    }

}