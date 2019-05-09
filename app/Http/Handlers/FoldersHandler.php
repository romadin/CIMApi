<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 24-1-2019
 * Time: 00:51
 */

namespace App\Http\Handlers;


use App\Models\Folder\Folder;
use App\Models\Template\Template;
use App\Models\Template\TemplateItem;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FoldersHandler
{
    const FOLDERS_TABLE = 'folders';
    const PROJECT_TABLE = 'projects';
    const FOLDERS_LINK_TABLE = 'folders_has_folders';

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

    public function createFoldersTemplate(array $templateContent, Template $template, $projectId = null, $parentFolderId = null): void
    {
        foreach ($templateContent as $folderTemplate) {
            $row = [
                'name' => $folderTemplate->getName(),
                'projectId' => $projectId,
                'mainFolder' => $folderTemplate->getName() === 'BIM-Uitvoeringsplan' ? true : false,
                'fromTemplate' => true,
            ];

            $newFolderId = DB::table(self::FOLDERS_TABLE)->insertGetId($row);
            if ($projectId === null) {
                // if project id is null then its a link between folders, so folder gets a sub folder.
                $this->insertLink($parentFolderId, $newFolderId, $folderTemplate->getOrder(), self::FOLDERS_LINK_TABLE,  'folderSubId');
                // on the subFolder we need to attach documents with the given template
                $templateContent = array_filter($template->getSubDocuments(), function($parentFolder) use ($folderTemplate) {
                    return $parentFolder->getName() === $folderTemplate->getName();
                });
                $templateContent = reset($templateContent);
                $this->documentsHandler->createDocumentsWithTemplate($newFolderId, $templateContent->getItems());
            }
        }

        // If there is a parent folder id we dont want to set sub folders.
        if ( $parentFolderId === null ) {
            // @todo make a more variable sub folder template, now its hardcoded.
            $this->setSubFolderFromProjectId($projectId, $template);
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
     * @param Template $template
     * @return bool | \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function setSubFolderFromProjectId(int $projectId, Template $template)
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

        $this->createFoldersTemplate($template->getHeadlines(), $template, null, $result->id);
        $this->documentsHandler->createDocumentsWithTemplate($result->id, $template->getChapters());
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