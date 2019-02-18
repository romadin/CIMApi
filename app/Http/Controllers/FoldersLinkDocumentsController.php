<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 31-1-2019
 * Time: 22:29
 */

namespace App\Http\Controllers;

use App\Http\Handlers\FoldersLinkDocumentsHandler;
use Illuminate\Http\Request;

class FoldersLinkDocumentsController extends ApiController
{
    private $foldersLinksHandler;

    public function __construct(FoldersLinkDocumentsHandler $foldersLinkDocumentsHandler)
    {
        $this->foldersLinksHandler = $foldersLinkDocumentsHandler;
    }

    public function deleteFoldersLinkDocuments(Request $request, int $folderId, int $documentId)
    {
        return $this->getReturnValueObject($request, $this->foldersLinksHandler->deleteLink($folderId, $documentId));
    }

}