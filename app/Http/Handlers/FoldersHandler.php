<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 24-1-2019
 * Time: 00:51
 */

namespace App\Http\Handlers;


use App\Models\Company\Company;
use App\Models\Folder\Folder;
use App\Models\Headline\Headline;
use App\Models\Template\Template;
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FoldersHandler
{
    const FOLDERS_TABLE = 'folders';
    const PROJECT_TABLE = 'projects';

    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;
    /**
     * @var HeadlinesHandler
     */
    private $headlinesHandler;
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;

    private $folderCache = [];

    public function __construct(DocumentsHandler $documentsHandler, HeadlinesHandler $headlinesHandler, ChaptersHandler $chaptersHandler)
    {
        $this->documentsHandler = $documentsHandler;
        $this->headlinesHandler = $headlinesHandler;
        $this->chaptersHandler = $chaptersHandler;
    }

    public function getFoldersByWorkFunction(WorkFunction $workFunction)
    {
        try {
            $result = DB::table(self::FOLDERS_TABLE)
                ->select([
                    self::FOLDERS_TABLE.'.id',
                    self::FOLDERS_TABLE.'.name',
                    self::FOLDERS_TABLE.'.fromTemplate',
                    WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE. '.order',
                ])
                ->where(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE. '.workFunctionId', $workFunction->getId())
                ->join(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE,self::FOLDERS_TABLE. '.id', '=', WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE. '.folderId'  )
                ->get();
            if ( $result === null) {
                return [];
            }
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        $folders = [];

        foreach ($result as $folder) {
            array_push($folders, $this->makeFolder($folder, $workFunction));
        }

        return $folders;
    }

    public function getFoldersByCompany(Company $company)
    {
        try {
            $result = DB::table(self::FOLDERS_TABLE)
                ->select([
                    self::FOLDERS_TABLE.'.id',
                    self::FOLDERS_TABLE.'.name',
                    self::FOLDERS_TABLE.'.fromTemplate',
                    CompaniesHandler::TABLE_LINK_FOLDER. '.order',
                ])
                ->where(CompaniesHandler::TABLE_LINK_FOLDER. '.companyId', $company->getId())
                ->join(CompaniesHandler::TABLE_LINK_FOLDER,self::FOLDERS_TABLE. '.id', '=', CompaniesHandler::TABLE_LINK_FOLDER. '.folderId')
                ->get();
            if ( $result === null) {
                return [];
            }
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        $folders = [];

        foreach ($result as $folder) {
            array_push($folders, $this->makeFolder($folder, $company));
        }

        return $folders;
    }

    /**
     * @param int $id
     * @param WorkFunction|Company|null $parent
     * @return Folder|Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getFolderById(int $id, $parent = null)
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

        return $this->makeFolder($folder, $parent);
    }

    /**
     * @param Headline[] $items
     * @param Template $template
     * @param WorkFunction $workFunction
     */
    public function createFoldersWithTemplateWorkFunction($items, Template $template, WorkFunction $workFunction): void
    {
        foreach ($items as $item) {
            $row = [
                'name' => $item->getName(),
                'fromTemplate' => true,
            ];

            /** @var Folder $newFolder */
            $newFolder = $this->postFolder($row, $workFunction, $item->getOrder());

            $this->insertLink($workFunction->getId(), $newFolder->getId(), $item->getOrder(), WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE,  'folderId');

            /** Create documents from the template to add to the folder */
            $this->documentsHandler->createDocumentsWithTemplate($newFolder, $item->getChapters(), DocumentsHandler::DOCUMENT_LINK_FOLDER_TABLE);
        }
    }

    /**
     * @param $data
     * @param WorkFunction $parent
     * @param int|null $order
     * @return Folder|Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function postFolder($data, WorkFunction $parent, ?int $order = null)
    {
        try {
            $id = DB::table(self::FOLDERS_TABLE)
                ->insertGetId($data);

            $order = $order !== null ? $order : $this->getHighestOrderFromWorkFunction($parent->getId()) + 1;
            $this->insertLink($parent->getId(), $id, $order, WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE, 'folderId');

        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return $this->getFolderById($id, $parent);
    }

    public function editFolder($data, int $id, WorkFunction $workFunction): Folder
    {
        try {
            DB::table(self::FOLDERS_TABLE)
                ->where('id', $id)
                ->update($data);
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 403);
        }

        return $this->getFolderById($id, $workFunction);
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
                $this->documentsHandler->deleteDocumentsByFolderId($folder);

                DB::table(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE)
                    ->where('folderId', $folder->getId())
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
     * @param Folder $folder
     * @return bool
     * @throws Exception
     */
    public function checkForNoConnections(Folder $folder): bool
    {
        try {
            $query = DB::table(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE)
                ->select(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE.'.folderId', DB::raw('count(*) as total'))
                ->where(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE.'.folderId', $folder->getId())
                ->groupBy(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE.'.folderId');
            $hasNoConnections = DB::table(CompaniesHandler::TABLE_LINK_FOLDER)
                ->select(CompaniesHandler::TABLE_LINK_FOLDER.'.folderId', DB::raw('count(*) as total'))
                ->union($query)
                ->where(CompaniesHandler::TABLE_LINK_FOLDER.'.folderId', $folder->getId())
                ->groupBy(CompaniesHandler::TABLE_LINK_FOLDER.'.folderId')
                ->get()->isEmpty();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

        return $hasNoConnections;
    }

    public function insertLink(int $workFunctionId, int $subItemId, int $order, string $table, string $subItemColumn)
    {
        try {
            // check if link already exist
            $result = DB::table($table)
                ->where('workFunctionId', $workFunctionId)
                ->where($subItemColumn, $subItemId)
                ->first();

            if ( $result === null ) {
                DB::table($table)->insert([
                    'workFunctionId' => $workFunctionId,
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

    /**
     * @param string $linkTable
     * @param string $linkIdName
     * @param int $linkId
     * @param int $folderId
     * @return Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     */
    public function deleteLink(string $linkTable, string $linkIdName, int $linkId, int $folderId)
    {
        try {
            DB::table($linkTable)
                ->where($linkIdName, $linkId)
                ->where('folderId', $folderId)
                ->delete();
        } catch (\Exception $e) {
            return response('FoldersHandler: There is something wrong with the database connection', 500);
        }

        return json_decode('Folder link deleted');
    }

    /**
     * @param $data
     * @param WorkFunction|Company|null $parent
     * @return Folder
     */
    private function makeFolder($data, $parent = null): Folder
    {
        if (isset($this->folderCache[$data->id])) {
            return $this->folderCache[$data->id];
        }
        $folder = new Folder(
            $data->id,
            $data->name,
            isset($data->order) ? $data->order : 0,
            $data->fromTemplate
        );

        if($parent) {
            $folder->setOrder($this->getOrder($folder, $parent));
        }

        $this->folderCache[$folder->getId()] = $folder;

        return $folder;
    }

    /**
     * @param Folder $folder
     * @param WorkFunction|Company $parent
     * @return Response|\Laravel\Lumen\Http\ResponseFactory|int
     */
    private function getOrder(Folder $folder, $parent)
    {
        if ($parent instanceof WorkFunction) {
            $parentIdName = 'workFunctionId';
            $table = WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE;
        } else {
            $parentIdName = 'companyId';
            $table = CompaniesHandler::TABLE_LINK_FOLDER;
        }

        try {
            $result = DB::table($table)
                ->select($table.'.order')
                ->where($parentIdName, $parent->getId())
                ->where( 'folderId', $folder->getId())
                ->first();
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return $result ? $result->order : 0;
    }

    private function getHighestOrderFromWorkFunction(int $workFunctionId)
    {
        try {
            $query = DB::table(WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE)
                ->select('order')
                ->where('workFunctionId', $workFunctionId);

            $result = DB::table(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE)
                ->select('order')
                ->where('workFunctionId', $workFunctionId)
                ->union($query)
                ->orderByDesc('order')
                ->first();
            if ($result == null) {
                return 0;
            }
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return $result->order;
    }

    private function getFolderLinks(Folder $folder): int
    {
        try {
            $result = DB::table(WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE)
                ->select('order')
                ->where( 'folderId', $folder->getId())
                ->get();
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return count($result);
    }

}