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
//use Elibyy\TCPDF\Facades\TCPDF;
use Exception;
use Illuminate\Http\Request;
use TCPDF;

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

            usort($documents, array($this, 'sortByOrder'));

            $html = '';
            foreach ($documents as $document) {
                $html .= '<h1>' . $document->getTitle() . '</h1>' . $document->getContent();
                $subDocuments = $document->getSubDocuments();
                usort($subDocuments, array($this, 'sortByOrder'));

                foreach ($subDocuments as $subDocument) {
                    $html .= '<h3 style="color: #80b8ff">' . $subDocument->getTitle() . '</h3>' . $subDocument->getContent();
                }
            }

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Nicola Asuni');
            $pdf->SetTitle('TCPDF Example 001');
            $pdf->SetSubject('TCPDF Tutorial');
            $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'BIM uitvoeringsplan', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
            $pdf->setFooterData(array(0,64,0), array(0,64,128));

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(2);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


            // set default font subsetting mode
            $pdf->setFontSubsetting(true);

            // Set font
            // dejavusans is a UTF-8 Unicode font, if you only need to
            // print standard ASCII chars, you can use core fonts like
            // helvetica or times to reduce file size.
            $pdf->SetFont('helvetica', '', 11, '', true);

            // Add a page
            // This method has several options, check the source code documentation for more information.
            $pdf->AddPage();

            // set text shadow effect
            $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));


            // Print text using writeHTMLCell()
            $pdf->writeHTML($html);

            // set display mode. Fixes vertical scrolling
            $pdf->SetDisplayMode('default','OneColumn');
            $pdf->AddPage();

            // Close and output PDF document
            // This method has several options, check the source code documentation for more information.
            $pdf->Output('example_001.pdf', 'I');


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

    static function sortByOrder($a, $b){
        return $a->getOrder() === $b->getOrder() ? 0 : ($a->getOrder() < $b->getOrder()) ? -1 : 1;
    }

}
