<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 29-1-2019
 * Time: 20:22
 */

namespace App\Http\Controllers\Documents;


use App\Http\Controllers\ApiController;
use App\Http\Handlers\CompaniesHandler;
use App\Http\Handlers\DocumentsHandler;
use App\Http\Handlers\FoldersHandler;
use App\Http\Handlers\TemplatesHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use Exception;
use Illuminate\Http\Request;

class DocumentsController extends ApiController
{
    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;

    /**
     * @var TemplatesHandler
     */
    private $templateHandler;

    /**
     * @var FoldersHandler
     */
    private $foldersHandler;

    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;

    /**
     * @var CompaniesHandler
     */
    private $companiesHandler;

    public function __construct(DocumentsHandler $documentsHandler, TemplatesHandler $templatesHandler, FoldersHandler $foldersHandler, WorkFunctionsHandler $workFunctionsHandler, CompaniesHandler $companiesHandler)
    {
        $this->documentsHandler = $documentsHandler;
        $this->templateHandler = $templatesHandler;
        $this->foldersHandler = $foldersHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->companiesHandler = $companiesHandler;
    }

    public function getDocuments(Request $request)
    {
        try {
            if ( $request->input('folderId') ) {
                $folder = $this->foldersHandler->getFolderById($request->input('folderId'));
                return $this->getReturnValueArray($request, $this->documentsHandler->getDocumentsFromFolder($folder));
            } else if ( $request->input('companyId') && $request->input('workFunctionId')) {
                try {
                    $company = $this->companiesHandler->getCompanyById($request->input('companyId'));
                    $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
                } catch (\Exception $e) {
                    return response($e->getMessage(), 400);
                }
                return $this->documentsHandler->getDocumentsFromCompany($company, $workFunction);
            } else if ( $request->input('workFunctionId') ){
                $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
                return $this->documentsHandler->getDocumentsFromWorkFunction($workFunction);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return response('No parent id has been given', 501);
    }

    public function getDocument(Request $request, $id)
    {
        try {
            if ($request->input('workFunctionId')) {
                $parent = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
            } else if ($request->input('documentId')) {
                $parent = $this->documentsHandler->getDocumentById($request->input('documentId'));
            } else {
                return response('No parent id has been given', 501);
            }


            $document = $this->documentsHandler->getDocumentById($id, $parent);
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return $document;
    }

    public function postDocuments(Request $request, $id = null)
    {
        if ( $id !== null ) {
            return $this->editDocument($request, $id);
        }

        try {
            $document = $this->documentsHandler->postDocument($request->post());
            $child = ['name' => 'documentId', 'id' => $document->getId()];

            if ($request->input('workFunctionId')) {
                $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
                $parent = ['name' => 'workFunctionId', 'id' => $workFunction->getId()];
                $this->documentsHandler->setDocumentLink($parent, $child, WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE);
            } else if ($request->input('documentId')) {
                $parentDocument = $this->documentsHandler->getDocumentById($request->input('documentId'));
                $parent = ['name' => 'documentId', 'id' => $parentDocument->getId()];
                $child['name'] = 'subDocumentId';
                $this->documentsHandler->setDocumentLink($parent, $child, DocumentsHandler::DOCUMENT_LINK_DOCUMENT_TABLE);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return $document;
    }

    public function deleteDocument(Request $request, int $id)
    {
        if ($request->input('companyId') && $request->input('workFunctionId')) {
            $whereStatement = [
                ['workFunctionId', '=', $request->input('workFunctionId')],
                ['companyId', '=', $request->input('companyId')]
            ];

            return $this->documentsHandler->deleteDocumentLink(DocumentsHandler::DOCUMENT_LINK_COMPANY_WORK_FUNCTION, $whereStatement, $id);
        } else if ($request->input('workFunctionId')) {
            $whereStatement = [['workFunctionId', '=', $request->input('workFunctionId')]];
            return $this->documentsHandler->deleteDocumentLink(WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE, $whereStatement, $id);
        }

        $done = $this->documentsHandler->deleteDocument($id);
        if ($done) {
            return json_encode('document Deleted');
        }
        return response('Trying to delete document ' .  $id . 'went wrong', 403);
    }

    public function uploadImage(Request $request, $id)
    {
        $image = $this->documentsHandler->postImage($id, $request->file('file'));
        return $this->getReturnValueObject($request, $image);
    }

    private function editDocument(Request $request, $id)
    {
        return $this->getReturnValueObject($request, $this->documentsHandler->editDocument($request->post(), $id));
    }

}