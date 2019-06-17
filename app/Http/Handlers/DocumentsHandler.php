<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 29-1-2019
 * Time: 20:21
 */

namespace App\Http\Handlers;

use App\Models\Chapter\Chapter;
use App\Models\Document\Document;
use App\Models\Folder\Folder;
use App\Models\WorkFunction\WorkFunction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DocumentsHandler
{
    const DOCUMENT_TABLE = 'documents';
    const DOCUMENT_IMAGE_TABLE = 'document_image';
    const DOCUMENT_LINK_FOLDER_TABLE = 'folders_has_documents';

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
                self::DOCUMENT_TABLE.'.fromTemplate',
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
     * @param WorkFunction $workFunction
     * @return Document[]
     */
    public function getDocumentsFromWorkFunction(WorkFunction $workFunction)
    {
        $linkTable = WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE;
        $documentsResult = DB::table($linkTable)
            ->select([
                self::DOCUMENT_TABLE.'.id', self::DOCUMENT_TABLE.'.originalName',
                self::DOCUMENT_TABLE.'.name', self::DOCUMENT_TABLE.'.content',
                self::DOCUMENT_TABLE.'.fromTemplate',
                $linkTable.'.workFunctionId',
                $linkTable. '.order',
            ])
            ->where($linkTable.'.workFunctionId', '=', $workFunction->getId())
            ->join(self::DOCUMENT_TABLE, $linkTable. '.documentId', '=', self::DOCUMENT_TABLE. '.id'  )
            ->get();

        $documents = [];

        forEach ( $documentsResult as $document ) {
            $document->parentId = $workFunction->getId();
            array_push($documents, $this->makeDocument($document));
        }

        return $documents;
    }

    public function getDocumentById(int $id)
    {
        $documentResult = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
            ->select([
                self::DOCUMENT_TABLE.'.id', self::DOCUMENT_TABLE.'.originalName',
                self::DOCUMENT_TABLE.'.name', self::DOCUMENT_TABLE.'.content',
                self::DOCUMENT_TABLE.'.fromTemplate',
                self::DOCUMENT_LINK_FOLDER_TABLE.'.folderId',
                self::DOCUMENT_LINK_FOLDER_TABLE. '.order',
            ])
            ->where(self::DOCUMENT_TABLE.'.id', '=', $id)
            ->join(self::DOCUMENT_TABLE, self::DOCUMENT_LINK_FOLDER_TABLE. '.documentId', '=', self::DOCUMENT_TABLE. '.id'  )
            ->first();

        $this->setFoldersId($documentResult);
        return $this->makeDocument($documentResult);
    }

    /**
     * @param array $postData
     * @param Folder|WorkFunction $parent
     * @param string $linkTable
     * @param null|int $order
     * @return Document|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function postDocument(array $postData, $parent, $linkTable, ?int $order = null)
    {
        $parentIdName = $parent instanceof Folder ? 'folderId' : 'workFunctionId';
        $postData['originalName'] = $postData['name'];
        try {
            $id = DB::table(self::DOCUMENT_TABLE)
                ->insertGetId($postData);

            // insert the link folder has document en set order.
            DB::table($linkTable)
                ->insert([
                    $parentIdName => $parent->getId(),
                    'documentId' => $id,
                    'order' => isset($order) ? $order : $this->getHighestOrderFromFolder($parent->getId()) + 1,
                ]);

            $document = $this->getDocumentById($id);
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
        return $document;
    }

    /**
     * Create document from an given template.
     * @param Folder|WorkFunction $parentItem
     * @param Chapter[] $documents
     * @param string $linkTable
     * @return Document[]
     */
    public function createDocumentsWithTemplate($parentItem, $documents, $linkTable)
    {
        foreach ($documents as $document) {
            $row = [
                'originalName' => $document->getName(),
                'name' => null,
                'content' => $document->getContent(),
                'fromTemplate' => true,
            ];
            $this->postDocument($row, $parentItem, $linkTable, $document->getOrder());
        }

        if($parentItem instanceof Folder) {
            return $this->getDocumentsFromFolder($parentItem->getId());
        }

        return $this->getDocumentsFromWorkFunction($parentItem);
    }

    public function editDocument(array $postData, int $id)
    {
        try {
            DB::table(self::DOCUMENT_TABLE)
                ->where('id', $id)
                ->update($postData);

            $document = $this->getDocumentById($id);
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }
        return $document;
    }

    public function deleteDocumentsByFolderId(Folder $folder)
    {
        $documents = $this->getDocumentsFromFolder($folder->getId());
        try {
            foreach ($documents as $document) {
                DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                    ->where('documentId', $document->getId())
                    ->where('folderId', $folder->getId())
                    ->delete();

                $document->setParentIds(array_diff($document->getParentIds(), [$folder->getId()]));

                if ( empty($document->getParentIds()) ) {
                    DB::table(self::DOCUMENT_TABLE)->delete($document->getId());
                }
            }
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }

        return true;
    }

    public function deleteDocument(int $id)
    {
        try {
            DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->where('documentId', $id)
                ->delete();

            DB::table(self::DOCUMENT_TABLE)->delete($id);
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }

        return true;
    }

    public function getHighestOrderFromFolder(int $folderId)
    {
        try {
            $result = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->select('order')
                ->where('folderId', $folderId)
                ->orderByDesc('order')
                ->first();

            if ($result == null) {
                return 0;
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return response('There is something wrong with the connection', 403);
        }

        return $result->order;
    }

    public function getDocumentImage($id)
    {
        $image = DB::table(self::DOCUMENT_IMAGE_TABLE)
            ->where('id', $id)
            ->first();
        $base64 = 'data:'. $image->extension . ';base64,' . base64_encode($image->image);
        return json_encode(['imageUrl' => $base64]);
    }

    public function postImage(int $documentId, UploadedFile $image)
    {
        $data = [];

        $data['image'] = $image->openFile()->fread($image->getSize());
        $data['imageName'] = $image->getClientOriginalName();
        $data['extension'] = $image->getClientMimeType();
        $data['pathName'] = $image->getPathName();
        $data['size'] = $image->getSize();
        $data['documentId'] = $documentId;

        try {
            $imageId = DB::table(self::DOCUMENT_IMAGE_TABLE)
                ->insertGetId($data);
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }

        return $this->getDocumentImage($imageId);

    }

    private function makeDocument($data): Document
    {
        $document = new Document(
            $data->id,
            $data->originalName,
            $data->name,
            $data->content,
            $data->order,
            $data->fromTemplate
        );

        $document->setParentIds(is_array($data->parentId) ? $data->parentId : [$data->parentId]);

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
            $documentResult->parentId = $idContainer;
        }
        return $documentResult;
    }

}
