<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 22-1-2019
 * Time: 12:53
 */

namespace App\Http\Controllers\Folders;


use App\Http\Handlers\CompaniesHandler;
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

    /**
     * @var CompaniesHandler
     */
    private $companiesHandler;

    public function __construct(
        FoldersHandler $foldersHandler,
        FoldersLinkDocumentsHandler $foldersLinkDocumentsHandler,
        WorkFunctionsHandler $workFunctionsHandler,
        CompaniesHandler $companiesHandler)
    {
        $this->foldersHandler = $foldersHandler;
        $this->foldersLinkDocumentsHandler = $foldersLinkDocumentsHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->companiesHandler = $companiesHandler;
    }

    public function getFolders(Request $request)
    {
        if ( $request->input('workFunctionId')) {
            $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
            return $this->getReturnValueArray($request, $this->foldersHandler->getFoldersByWorkFunction($workFunction));
        } else if ($request->input('companyId')) {
            try {
                $company = $this->companiesHandler->getCompanyById($request->input('companyId'));
            } catch (\Exception $e) {
                return response($e->getMessage(), 400);
            }
            return $this->foldersHandler->getFoldersByCompany($company);
        }

        return response('No parent id is given', 404);
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

    public function deleteFolders(Request $request, $id)
    {
        if ($request->input('workFunctionId') && $request->input('companyId')) {
            return response('no parent id given', 404);
        }

        $parentIdName = $request->input('workFunctionId') ? 'workFunctionId' : 'companyId';
        $parentId = $request->input($parentIdName);
        try {
            if ($request->input('workFunctionId')) {
                $parent = $this->workFunctionsHandler->getWorkFunction($parentId);
                $linkTable = WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE;
            } else {
                $parent = $this->companiesHandler->getCompanyById($parentId);
                $linkTable = CompaniesHandler::TABLE_LINK_FOLDER;
            }

            $folder = $this->foldersHandler->getFolderById($id, $parent);
            $this->foldersHandler->deleteLink($linkTable, $parentIdName, $parentId, $folder->getId() );

            if ($this->foldersHandler->checkForNoConnections($folder)) {
                return $this->foldersHandler->deleteFolder($folder);
            }else {
                return json_decode('link deleted');
            }
        }catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }
    }
}
