<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 31-1-2019
 * Time: 22:32
 */

namespace App\Http\Handlers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FoldersLinkDocumentsHandler
{
    const FOLDER_LINK_DOCUMENT_TABLE = 'folders_has_documents';
    /**
     * @var FoldersHandler
     */
    private $foldersHandler;

    /**
     * FoldersLinkDocumentsHandler constructor.
     * @param FoldersHandler $foldersHandler
     */
    public function __construct(FoldersHandler $foldersHandler)
    {
        $this->foldersHandler = $foldersHandler;
    }

    public function linkDocumentsToFolder($documentsId, int $folderId)
    {
        foreach ($documentsId as $documentId) {
            $this->insertLink($folderId, $documentId);
        }
        return $this->foldersHandler->getFolderById($folderId);
    }

    public function deleteLink(int $folderId, int $documentId)
    {
        try {
            $deleted = DB::table(self::FOLDER_LINK_DOCUMENT_TABLE)
                ->where('folderId', $folderId)
                ->where('documentId', $documentId)->delete();
            if ( !$deleted ) {
                return response('FoldersLinkDocumentsHandler: The link did not get deleted', 409);
            }
        } catch (\Exception $e) {
            return response('FoldersLinkDocumentsHandler: There is something wrong with the database connection', 500);
        }

        return $this->foldersHandler->getFolderById($folderId);
    }

    private function insertLink(int $folderId, int $documentId)
    {
        try {
            $result = DB::table(self::FOLDER_LINK_DOCUMENT_TABLE)
                ->where('folderId', $folderId)
                ->where('documentId', $documentId)->first();
            if ( $result === null ) {
                DB::table(self::FOLDER_LINK_DOCUMENT_TABLE)->insert([
                    'folderId' => $folderId,
                    'documentId' => $documentId
                ]);
            }
        } catch (\Exception $e) {
            return response('FoldersLinkDocumentsHandler: There is something wrong with the database connection', 500);
        }
        return true;
    }

}