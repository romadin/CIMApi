<?php


namespace App\Http\Controllers\Documents;


use App\Http\Handlers\WorkFunctionsHandler;
use App\Http\Handlers\OrganisationHandler;
use App\Http\Handlers\DocumentsHandler;
use App\Models\Document\Document;
use Illuminate\Http\Request;
use App\Models\Tcpdf\MyPdf;
use Exception;

class PdfController
{
    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;

    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;


    /**
     * @var OrganisationHandler
     */
    private $organisationHandler;

    public function __construct(
        DocumentsHandler $documentsHandler,
        WorkFunctionsHandler $workFunctionsHandler,
        OrganisationHandler $organisationHandler)
    {
        $this->documentsHandler = $documentsHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->organisationHandler = $organisationHandler;
    }

    public function createPdf($organisationId, Request $request) {
        try {
            $organisation = $this->organisationHandler->getOrganisationById($organisationId);

            $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false, $organisation);

            $html = '';
            // Add a page
            $pdf->AddPage();

            $pdf->writeHTML($request->input('content'));

            // Close and output PDF document
            // This method has several options, check the source code documentation for more information.
//            return $pdf->Output($request->input('documentName') . '.pdf', 'I');
            return $pdf->Output($request->input('documentName'), 'S');
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    public function createPdfFromDocuments($workFunctionId, $organisationId)
    {
        try {
            $organisation = $this->organisationHandler->getOrganisationById($organisationId);

            $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false, $organisation);

            $workFunction = $this->workFunctionsHandler->getWorkFunction($workFunctionId);
            $documents =  $this->documentsHandler->getDocumentsFromWorkFunction($workFunction);

            usort($documents, array($this, 'sortByOrder'));

            foreach ($documents as $document) {
                $html = '';
                // Add a page
                $pdf->AddPage();
                // start transaction
                $pdf->startTransaction();

                $html .= '<h1>' . $document->getTitle() . '</h1>' . $this->getAlteredContent($document);
                $pdf->writeHTML($html, false, true, true, false, '');

                $subDocuments = $document->getSubDocuments();
                usort($subDocuments, array($this, 'sortByOrder'));
                foreach ($subDocuments as $subDocument) {
                    $htmlSubDocuments = '';
                    $pdf->startTransaction();
                    $htmlSubDocuments .= '<h3 style="color: #80b8ff">' . $subDocument->getTitle() . '</h3>' . $this->getAlteredContent($subDocument);
                    // Print text using writeHTML()
                    $pdf->writeHTML($htmlSubDocuments);

                    // The y exceeded the page height. It wil automatically break to a new page
                    if ($pdf->getY() > ($pdf->getPageHeight() - 30)) {

                        // rollback so that we dont have weird breaks in our pages.
                        $pdf->rollbackTransaction(true);
                        // add new page
                        $pdf->AddPage();
                        // Print text using writeHTML()
                        $pdf->writeHTML($htmlSubDocuments);

                    }
                }
            }

            // Close and output PDF document
            // This method has several options, check the source code documentation for more information.
            return $pdf->Output('BIM uitvoeringsplan.pdf', 'S');
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    /**
     * Alter the image's string in the content string.
     * @param Document $document
     * @return string | null
     */
    private function getAlteredContent($document) {
        $matches = [];
        $content = $document->getContent();
        $match = preg_match_all( '/(?<=src=")(.*?)(?=")/' , $content, $matches);
        $content = $match ? $this->getImagesFromContent($matches, $content) : $content;
        return $content;
    }

    private function getImagesFromContent(array $matches, $content):string {
        foreach ($matches[0] as $imgBase64) {
            $imageContent = file_get_contents($imgBase64);
            $tempPath = tempnam(sys_get_temp_dir(), 'prefix');
            file_put_contents ($tempPath, $imageContent);

            // replace the image with a temp path for the pdf
            $content = str_replace($imgBase64, $tempPath, $content);

            // remove the styling width 100% for the pdf because the pdf breaks the images.
            // Now we get the full width/height from the picture.
            $content = str_replace('width:100%', '', $content);
        }
        return $content;
    }

    /**
     * sort by the document order property.
     * @param Document $a
     * @param Document $b
     * @return int
     */
    static function sortByOrder($a, $b){
        return $a->getOrder() === $b->getOrder() ? 0 : ($a->getOrder() < $b->getOrder()) ? -1 : 1;
    }
}
