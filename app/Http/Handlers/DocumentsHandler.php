<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 29-1-2019
 * Time: 20:21
 */

namespace App\Http\Handlers;

use App\Models\Document\Document;
use Illuminate\Support\Facades\DB;

class DocumentsHandler
{
    const DOCUMENT_TABLE = 'documents';
    const DOCUMENT_LINK_FOLDER_TABLE = 'folders_has_documents';

    //@todo need a better way for templating
    const defaultDocumentTemplate = ['Projectgegevens', 'Doelstelling', 'Proces', 'Normen', 'Voorwaarden', 'BIM toepassing', 'Modeloverzicht'];

    /**
     * @param int $folderId
     * @return Document[]
     */
    public function getDocumentsFromFolder(int $folderId)
    {
        $documentsResult = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
            ->select([self::DOCUMENT_TABLE.'.id', self::DOCUMENT_TABLE.'.name', self::DOCUMENT_TABLE.'.content', self::DOCUMENT_LINK_FOLDER_TABLE.'.folderId' ])
            ->where(self::DOCUMENT_LINK_FOLDER_TABLE.'.folderId', '=', $folderId)
            ->join(self::DOCUMENT_TABLE, self::DOCUMENT_LINK_FOLDER_TABLE. '.documentId', '=', self::DOCUMENT_TABLE. '.id'  )
            ->get();

        $documents = [];

        forEach ( $documentsResult as $document ) {
            $foldersId = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->select('folderId' )
                ->where('documentId', '=', $document->id)
                ->where('folderId', '!=', $folderId)
                ->get();
            if( $foldersId->isNotEmpty() ) {
                $idContainer = [$folderId];
                foreach ($foldersId as $item) {
                    array_push($idContainer, $item->folderId);
                }
                $document->folderId = $idContainer;
            }
            array_push($documents, $this->makeDocument($document));
        }

        return $documents;
    }

    /**
     * Create document from an given template.
     * @param int $folderId
     * @param array | string $template
     * @return Document[]
     */
    public function createDocumentsWithTemplate(int $folderId, $template)
    {
        $template = $template !== 'default' ?: self::defaultDocumentTemplate;
        $newDocumentsId = [];
        foreach ($template as $documentName) {
            $row = [
                'name' => $documentName,
                'content' => null
            ];
            array_push($newDocumentsId, DB::table(self::DOCUMENT_TABLE)->insertGetId($row));
        }
        foreach ($newDocumentsId as $id) {
            DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->insert([
                    'folderId' => $folderId,
                    'documentId' => $id
                ]);
        }

        return $this->getDocumentsFromFolder($folderId);
    }

    public function deleteDocumentsByFolderId(int $folderId)
    {
        $documents = $this->getDocumentsFromFolder($folderId);
        try {
            foreach ($documents as $document) {
                DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                    ->where('documentId', $document->getId())
                    ->where('folderId', $folderId)
                    ->delete();

                $document->setParentFolderIds(array_diff($document->getParentFolderIds(), [$folderId]));

                if ( empty($document->getParentFolderIds()) ) {
                    DB::table(self::DOCUMENT_TABLE)->delete($document->getId());
                }
            }
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 403);
        }

        return true;
    }

    private function makeDocument($data): Document
    {
        $foldersId = is_array($data->folderId) ? $data->folderId : [$data->folderId];
        $document = new Document(
            $data->id,
            $data->name,
            $data->content,
            $foldersId
        );

        return $document;
    }

}
