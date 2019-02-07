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
    const defaultDocumentTemplate = [
        ['name' => 'Projectgegevens', 'order' => 1],
        ['name' => 'Doelstelling', 'order' => 2],
        ['name' => 'Proces', 'order' => 3],
        ['name' => 'Normen', 'order' => 4],
        ['name' => 'Voorwaarden', 'order' => 5],
        ['name' => 'BIM toepassing', 'order' => 7],
        ['name' => 'Modeloverzicht', 'order' => 12],
    ];

    /**
     * @param int $folderId
     * @return Document[]
     */
    public function getDocumentsFromFolder(int $folderId)
    {
        $documentsResult = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
            ->select([
                self::DOCUMENT_TABLE.'.id', self::DOCUMENT_TABLE.'.originalName',
                self::DOCUMENT_TABLE.'.name', self::DOCUMENT_TABLE.'.content',
                self::DOCUMENT_LINK_FOLDER_TABLE.'.folderId',
                self::DOCUMENT_LINK_FOLDER_TABLE. '.order',
            ])
            ->where(self::DOCUMENT_LINK_FOLDER_TABLE.'.folderId', '=', $folderId)
            ->join(self::DOCUMENT_TABLE, self::DOCUMENT_LINK_FOLDER_TABLE. '.documentId', '=', self::DOCUMENT_TABLE. '.id'  )
            ->get();

        $documents = [];

        forEach ( $documentsResult as $document ) {
            $document = $this->setFoldersId($document);
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
        foreach ($template as $documentTemplate) {
            $row = [
                'originalName' => $documentTemplate['name'],
                'name' => null,
                'content' => null
            ];
            $newDocumentID = DB::table(self::DOCUMENT_TABLE)->insertGetId($row);

            // insert the link folder has document en set order.
            DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->insert([
                    'folderId' => $folderId,
                    'documentId' => $newDocumentID,
                    'order' => $documentTemplate['order'],
                ]);
        }
        return $this->getDocumentsFromFolder($folderId);
    }

    public function editDocument(array $postData, int $id)
    {
        try {
            DB::table(self::DOCUMENT_TABLE)
                ->where('id', $id)
                ->update($postData);

            $updatedDocument = DB::table(self::DOCUMENT_TABLE)->where('id', $id)->first();
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }
        $updatedDocument = $this->setFoldersId($updatedDocument);
        return $this->makeDocument($updatedDocument);
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
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }

        return true;
    }

    private function makeDocument($data): Document
    {
        $foldersId = is_array($data->folderId) ? $data->folderId : [$data->folderId];

        $document = new Document(
            $data->id,
            $data->originalName,
            $data->name,
            $data->content,
            $foldersId,
            $data->order
        );

        return $document;
    }

    /**
     * Set the linked folders id to the document result. So that we can put it in the document model.
     * @param $documentResult
     * @return \Illuminate\Database\Eloquent\Model | object
     */
    private function setFoldersId($documentResult)
    {
        $foldersId = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
            ->select('folderId')
            ->where('documentId', '=', $documentResult->id)
            ->get();

        if( $foldersId->isNotEmpty() ) {
            $idContainer = [];
            foreach ($foldersId as $item) {
                array_push($idContainer, $item->folderId);
            }
            $documentResult->folderId = $idContainer;
        }
        return $documentResult;
    }

}
