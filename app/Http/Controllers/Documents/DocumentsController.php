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
use App\Http\Handlers\TemplatesHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use Elibyy\TCPDF\Facades\TCPDF;
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
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;

    /**
     * @var CompaniesHandler
     */
    private $companiesHandler;

    public function __construct(DocumentsHandler $documentsHandler, TemplatesHandler $templatesHandler, WorkFunctionsHandler $workFunctionsHandler, CompaniesHandler $companiesHandler)
    {
        $this->documentsHandler = $documentsHandler;
        $this->templateHandler = $templatesHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->companiesHandler = $companiesHandler;
    }

    public function getDocuments(Request $request)
    {
        try {
            if ( $request->input('companyId') && $request->input('workFunctionId')) {
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
            } else if ( $request->input('documentId') ){
                $parentDocument = $this->documentsHandler->getDocumentById($request->input('documentId'));
                return $this->documentsHandler->getSubDocuments($parentDocument);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return response('No parent id has been given', 501);
    }

    public function createPdf(Request $request)
    {
        try {
            $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
            $documents =  $this->documentsHandler->getDocumentsFromWorkFunction($workFunction);

            TCPDF::SetTitle('hello world');
            TCPDF::AddPage();
            TCPDF::Write(0, 'Hello World');
            TCPDF::Output('hello_world.pdf');

        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return response('Cannot create pdf', 501);
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
        try {
            if ( $id !== null ) {
                return $this->editDocument($request, $id);
            } else if ($request->input('documents') && $request->input('companyId') && $request->input('workFunctionId')) {
                // We only want to set the link for a BATCH of documents to a company and the company needs to be linked to a work function.
                $company = $this->companiesHandler->getCompanyById($request->input('companyId'));
                $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
                $this->companiesHandler->addDocuments($company, $workFunction, $request->input('documents'));
                return $company;
            } else if ($request->input('documents') && $request->input('workFunctionId')) {
                // We only want to set the link for a BATCH of documents to the work function.
                return $this->setDocumentsLink($request->input('documents'),  $request->input('workFunctionId'));
            }

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
        try {
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
        } catch (Exception $e) {
            return response('Deleting the document went wrong', 403);
        }
        return response('Deleting the document went wrong', 404);
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

    /**
     * Only set the links between the batch documents and the work function.
     * @param int[] $documents
     * @param int $workFunctionId
     * @return \App\Models\WorkFunction\WorkFunction|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    private function setDocumentsLink($documents, int $workFunctionId) {
        try {
            $workFunction = $this->workFunctionsHandler->getWorkFunction($workFunctionId);
            foreach ($documents as $documentsId) {
                $child = ['name' => 'documentId', 'id' => $documentsId];
                $parent = ['name' => 'workFunctionId', 'id' => $workFunction->getId()];

                $this->documentsHandler->setDocumentLink($parent, $child, WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
        return json_encode('Link has been made');
    }
}
