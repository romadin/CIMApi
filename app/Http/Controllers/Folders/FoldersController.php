<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 22-1-2019
 * Time: 12:53
 */

namespace App\Http\Controllers\Folders;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Handlers\FoldersHandler;
use App\Http\Controllers\ApiController;
use App\Http\Handlers\WorkFunctionsHandler;
use App\Http\Handlers\FoldersLinkDocumentsHandler;

class FoldersController extends ApiController
{
    /**
     * @var FoldersHandler
     */
    private $foldersHandler;

    /**
     * @var FoldersLinkDocumentsHandler
     */
    private $foldersLinkDocumentsHandler;

    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;

    public function __construct(
        FoldersHandler $foldersHandler,
        FoldersLinkDocumentsHandler $foldersLinkDocumentsHandler,
        WorkFunctionsHandler $workFunctionsHandler)
    {
        $this->foldersHandler = $foldersHandler;
        $this->foldersLinkDocumentsHandler = $foldersLinkDocumentsHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
    }

    public function getFolders(Request $request)
    {
        if ( $request->input('workFunctionId') === null ) {
            return response('The workFunction id is missing', 404);
        }
        $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));

        return $this->getReturnValueArray($request, $this->foldersHandler->getFoldersByWorkFunction($workFunction));
    }

    public function getFolder(Request $request, $id)
    {
        if (empty($request->input('workFunctionId'))) {
            return response('no work function id given', 404);
        }
        $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));

        return $this->getReturnValueObject($request, $this->foldersHandler->getFolderById($id, $workFunction));
    }

    public function createFolder(Request $request)
    {
        if (empty($request->input('workFunctionId'))) {
            return response('no work function id given', 404);
        }
        $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));

        return $this->getReturnValueObject($request, $this->foldersHandler->postFolder($request->post(), $workFunction));

    }

    public function editFolder(Request $request, $id)
    {
        if (empty($request->input('workFunctionId'))) {
            return response('no work function id given', 404);
        }

        $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
        $postData = $request->post();

        if (isset($postData['subDocuments'])) {
            $failedResponse = $this->foldersLinkDocumentsHandler->linkDocumentsToFolder($postData['subDocuments'], $id);
            unset($postData['subDocuments']);

            if ($failedResponse instanceof Response) {
                return $failedResponse;
            }
        }

        if (empty($postData)) {
            return $this->getReturnValueObject($request, $this->foldersHandler->getFolderById($id, $workFunction));
        }

        return $this->getReturnValueObject($request, $this->foldersHandler->editFolder($postData,$id, $workFunction));
    }

    public function deleteFolders(Request $request, $id = null)
    {
        if (empty($request->input('workFunctionId'))) {
            return response('no work function id given', 404);
        }
        $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));

        if ( $id ) {
            $folder = $this->foldersHandler->getFolderById($id, $workFunction);
            $existingLinks = $this->foldersHandler->deleteLink($folder, $request->input('workFunctionId'));

            if($existingLinks === 0) {
                return $this->foldersHandler->deleteFolder($folder);
            }else {
                return json_decode('link deleted');
            }
        }
        return json_decode('folder deleted');
    }

    private function updateLink(int $folderId, int $subItemId, $postData)
    {
        // update the link between folder and sub folder or sub documents.
        // @todo still need to implement the function.
    }
}
