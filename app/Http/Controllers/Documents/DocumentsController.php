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
use Illuminate\Http\Request;

class DocumentsController extends ApiController
{

    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;

    public function __construct(DocumentsHandler $documentsHandler)
    {
        $this->documentsHandler = $documentsHandler;
    }

    public function getDocuments(Request $request)
    {
        if ( $request->input('folderId') ) {
            return $this->getReturnValueArray($request, $this->documentsHandler->getDocumentsFromFolder($request->input('folderId')));
        }
        return response('Not implemented', 501);
    }

    public function postDocuments(Request $request, $id = null)
    {
        if ( $id !== null ) {
            return $this->editDocument($request, $id);
        }

        if( $request->input('template') && $request->input('folderId') ) {
            $newDocuments = $this->documentsHandler->createDocumentsWithTemplate(
                $request->input('folderId'),
                $request->input('template'));
            return $this->getReturnValueArray($request, $newDocuments);
        }

        if ($request->input('folderId')) {
            return $this->documentsHandler->createDocument($request);

        }
        return response('Not implemented', 501);
    }

    public function deleteDocument(Request $request, $id )
    {
        $done = $this->documentsHandler->deleteDocument($id);
        if ($done) {
            return json_encode('document Deleted');
        }
        return response('Trying to delete document ' .  $id . 'went wrong', 403);
    }

    private function editDocument(Request $request, $id)
    {
        return $this->getReturnValueObject($request, $this->documentsHandler->editDocument($request->post(), $id));
    }

}