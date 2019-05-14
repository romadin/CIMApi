<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 29-1-2019
 * Time: 20:22
 */

namespace App\Http\Controllers\Documents;


use App\Http\Controllers\ApiController;
use App\Http\Handlers\DocumentsHandler;
use App\Http\Handlers\FoldersHandler;
use App\Http\Handlers\TemplatesHandler;
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

    public function __construct(DocumentsHandler $documentsHandler, TemplatesHandler $templatesHandler, FoldersHandler $foldersHandler)
    {
        $this->documentsHandler = $documentsHandler;
        $this->templateHandler = $templatesHandler;
        $this->foldersHandler = $foldersHandler;
    }

    public function getDocuments(Request $request)
    {
        if ( $request->input('folderId') ) {
            $folder = $this->foldersHandler->getFolderById($request->input('folderId'));
            return $this->getReturnValueArray($request, $this->documentsHandler->getDocumentsFromFolder($folder));
        }
        return response('Not implemented', 501);
    }

    public function postDocuments(Request $request, $id = null)
    {
        if ( $id !== null ) {
            return $this->editDocument($request, $id);
        }

        if (!$request->input('folderId')) {
            return response('parent folder id not given', 501);
        }

        return $this->documentsHandler->postDocument($request->post(), $this->foldersHandler->getFolderById($request->input('folderId')));
    }

    public function deleteDocument($id)
    {
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