<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 24-1-2019
 * Time: 00:51
 */

namespace App\Http\Handlers;


use App\Models\Folder\Folder;
use Illuminate\Support\Facades\DB;

class FoldersHandler
{
    const FOLDERS_TABLE = 'folders';
    const PROJECT_TABLE = 'projects';
    const FOLDERS_LINK_TABLE = 'folders_has_folders';

    //@todo need a better way for templating
    const defaultSubFolderTemplate = ['Model afspraak', 'Analyse', 'Planning', 'Informatiebehoefte', 'Over BIM'];

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
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return $this->makeFolder($folder);
    }

    public function createFoldersTemplate(array $template, $projectId = null, $parentFolderId = null): void
    {
        foreach ($template as $folderName) {
            $row = [
                'name' => $folderName,
                'projectId' => $projectId,
                'mainFolder' => $folderName === 'BIM-Uitvoeringsplan' ? true : false,
            ];
            $newFolderId = DB::table(self::FOLDERS_TABLE)->insertGetId($row);
            if ($projectId === null) {
                // if project id is null then its a link between folders, so folder gets a sub folder.
                $this->setLinkFolderHasSubFolder($parentFolderId, $newFolderId );
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

    public function deleteFolderByProjectId(Int $projectId)
    {
        try {
            $foldersId = DB::table(self::FOLDERS_TABLE)
                ->select(['id'])
                ->where('projectId', $projectId)
                ->get();

            foreach ($foldersId as $id) {
                if( $this->documentsHandler->deleteDocumentsByFolderId($id->id) )  {
                    DB::table(self::FOLDERS_TABLE)->delete($id->id);
                }
            }
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return true;
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

    private function setLinkFolderHasSubFolder(int $folderId, int $subFolderId)
    {
        if ($folderId === $subFolderId) {
            return response('FoldersHandler: cant give the same id', 400);
        }
        try {
            DB::table(self::FOLDERS_LINK_TABLE)
                ->insert([
                    'folderId' => $folderId,
                    'folderSubId' => $subFolderId
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
            ->select([self::FOLDERS_TABLE.'.id', self::FOLDERS_TABLE.'.name', self::FOLDERS_TABLE.'.projectId',self::FOLDERS_TABLE.'.on', self::FOLDERS_TABLE.'.mainFolder', ])
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
            $data->projectId,
            empty($subFolders) ? null : $subFolders
        );

        return $folder;
    }

}