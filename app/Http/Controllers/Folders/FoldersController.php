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
use App\Http\Controllers\ApiController;
use App\Http\Handlers\FoldersHandler;
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

    public function __construct(FoldersHandler $foldersHandler, FoldersLinkDocumentsHandler $foldersLinkDocumentsHandler)
    {
        $this->foldersHandler = $foldersHandler;
        $this->foldersLinkDocumentsHandler = $foldersLinkDocumentsHandler;
    }

    public function getFolders(Request $request)
    {
        if ( $request->input('projectId') === null ) {
            return response('The project id is missing', 404);
        }

        return $this->getReturnValueArray($request, $this->foldersHandler->getFoldersByProjectId($request->input('projectId')));
    }

    public function getFolder(Request $request, $id)
    {
        return $this->getReturnValueObject($request, $this->foldersHandler->getFolderById($id));
    }

    public function createFolder(Request $request)
    {
        if ($request->input('projectId') | $request->input('parentFolderId')) {
            return $this->getReturnValueObject($request, $this->foldersHandler->postFolder($request->post()));
        }

        return response('The project id or parent folder id is missing ', 404);
    }

    public function postFolders(Request $request, $id, $subItemId = null)
    {
        $postData = $request->post();
        if ($subItemId !== null ) {
            return $this->updateLink($id, $subItemId, $postData);
        }

        if (isset($postData['subFolders'])) {
            $failedResponse = $this->foldersHandler->setLinkFolderHasSubFolder($id,  $postData['subFolders']);
            unset($postData['subFolders']);
            if ($failedResponse instanceof Response) {
                return $failedResponse;
            }
        }
        if (isset($postData['subDocuments'])) {
            $failedResponse = $this->foldersLinkDocumentsHandler->linkDocumentsToFolder($postData['subDocuments'], $id);
            unset($postData['subDocuments']);

            if ($failedResponse instanceof Response) {
                return $failedResponse;
            }
        }

        if (empty($postData)) {
            return $this->getReturnValueObject($request, $this->foldersHandler->getFolderById($id));
        }

        return $this->getReturnValueObject($request, $this->foldersHandler->editFolder($postData,$id));
    }

    public function deleteFolders(Request $request, $id = null)
    {
        if ( $id ) {
            /** Check if we need to delete the links and not the main folder its self.  */
            if ( !empty($request->input('subFolders')) || !empty($request->input('subDocuments')) || !empty($request->input('parentFolderId')) ) {
                $subDocumentsId = ['subDocumentsId' => $request->input('subDocuments') ];
                $subFoldersId = ['subFoldersId' => $request->input('subFolders') ];
                $parentFolderId = ['parentFolderId' => $request->input('parentFolderId') ];

                $links = array_merge($subFoldersId, $subDocumentsId, $parentFolderId);
                $this->deleteLinksFromFolder($id, $links);
                return $this->getFolder($request, $id);
            }

            return $this->foldersHandler->deleteFolder($this->foldersHandler->getFolderById($id));
        }
        if ( ! empty($request->input('projectId'))) {
            return $this->foldersHandler->deleteFolders($request->input('projectId'));
        }
        return 'Deleted';
    }

    /**
     * Determine which link we need te delete.
     * @param int $folderId
     * @param int[] $links
     */
    private function deleteLinksFromFolder(int $folderId, $links) {
        if (! empty($links['subFoldersId'])) {
            foreach($links['subFoldersId'] as $subFolderId) {
                $this->foldersHandler->deleteSubFolderLink($folderId, $subFolderId);
            }
        }
        if (! empty($links['subDocumentsId'])) {
            foreach($links['subDocumentsId'] as $subDocumentId) {
                $this->foldersLinkDocumentsHandler->deleteLink($folderId, $subDocumentId);
            }
        }
        if (! empty($links['parentFolderId'])) {
            $this->foldersHandler->deleteSubFolderLink($links['parentFolderId'], $folderId);
        }
    }

    private function updateLink(int $folderId, int $subItemId, $postData)
    {
        // update the link between folder and sub folder or sub documents.
        // @todo still need to implement the function.
    }
}
