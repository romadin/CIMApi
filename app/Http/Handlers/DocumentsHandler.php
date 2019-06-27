<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 29-1-2019
 * Time: 20:21
 */

namespace App\Http\Handlers;

use App\Models\Chapter\Chapter;
use App\Models\Company\Company;
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
     * @param Folder $folder
     * @return Document[]
     */
    public function getDocumentsFromFolder(Folder $folder)
    {
        $documentsResult = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
            ->select([
                self::DOCUMENT_TABLE.'.id', self::DOCUMENT_TABLE.'.originalName',
                self::DOCUMENT_TABLE.'.name', self::DOCUMENT_TABLE.'.content',
                self::DOCUMENT_TABLE.'.fromTemplate',
                self::DOCUMENT_LINK_FOLDER_TABLE.'.folderId',
                self::DOCUMENT_LINK_FOLDER_TABLE. '.order',
            ])
            ->where(self::DOCUMENT_LINK_FOLDER_TABLE.'.folderId', '=', $folder->getId())
            ->join(self::DOCUMENT_TABLE, self::DOCUMENT_LINK_FOLDER_TABLE. '.documentId', '=', self::DOCUMENT_TABLE. '.id'  )
            ->get();

        $documents = [];

        forEach ( $documentsResult as $document ) {
            $document = $this->setFoldersId($document);
            array_push($documents, $this->makeDocument($document, $folder));
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
            array_push($documents, $this->makeDocument($document, $workFunction));
        }

        return $documents;
    }

    public function getDocumentsFromCompany(Company $company)
    {
        $linkTable = CompaniesHandler::TABLE_LINK_DOCUMENT;
        $documentsResult = DB::table($linkTable)
            ->select([
                self::DOCUMENT_TABLE.'.id', self::DOCUMENT_TABLE.'.originalName',
                self::DOCUMENT_TABLE.'.name', self::DOCUMENT_TABLE.'.content',
                self::DOCUMENT_TABLE.'.fromTemplate',
                $linkTable.'.companyId',
                $linkTable. '.order',
            ])
            ->where($linkTable.'.companyId', '=', $company->getId())
            ->join(self::DOCUMENT_TABLE, $linkTable. '.documentId', '=', self::DOCUMENT_TABLE. '.id')
            ->get();

        $documents = [];

        forEach ( $documentsResult as $document ) {
            $document->parentId = $company->getId();
            array_push($documents, $this->makeDocument($document, $company));
        }

        return $documents;
    }

    /**
     * @param int $id
     * @param WorkFunction|Folder|null $parent
     * @return Document|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     *
     */
    public function getDocumentById(int $id, $parent = null)
    {
        try {
            $documentResult = DB::table(self::DOCUMENT_TABLE)
                ->where(self::DOCUMENT_TABLE.'.id', '=', $id)
                ->first();
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
        return $this->makeDocument($documentResult, $parent);
    }

    /**
     * @param array $postData
     * @param Folder|WorkFunction $parent
     * @param string $linkTable
     * @param null|int $order
     * @return Document|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function postDocument(array $postData, $parent, $linkTable, int $order = null)
    {
        $parentIdName = $parent instanceof Folder ? 'folderId' : 'workFunctionId';
        $postData['originalName'] =  isset($postData['originalName']) ? $postData['originalName'] : $postData['name'];

        try {
            $id = DB::table(self::DOCUMENT_TABLE)
                ->insertGetId($postData);

            // insert the link folder|workFunction has document en set order.
            DB::table($linkTable)
                ->insert([
                    $parentIdName => $parent->getId(),
                    'documentId' => $id,
                    'order' => isset($order) ? $order : $this->getHighestOrderFromParent($parent->getId(), $linkTable, $parentIdName) + 1,
                ]);

            $document = $this->getDocumentById($id, $parent);
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
            return $this->getDocumentsFromFolder($parentItem);
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
        $documents = $this->getDocumentsFromFolder($folder);
        try {
            foreach ($documents as $document) {
                DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                    ->where('documentId', $document->getId())
                    ->delete();

                DB::table(self::DOCUMENT_TABLE)->delete($document->getId());
            }
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }

        return true;
    }

    public function deleteDocumentLink(string $linkTable, string $linkIdName, int $linkId, int $documentId)
    {
        try {
            DB::table($linkTable)
                ->where($linkIdName, $linkId)
                ->where('documentId', $documentId)
                ->delete();
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }

        return json_decode('Document link deleted');
    }

    public function deleteDocument(int $id)
    {
        try {
            DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->where('documentId', $id)
                ->delete();

            DB::table(WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE)
                ->where('documentId', $id)
                ->delete();

            DB::table(self::DOCUMENT_TABLE)->delete($id);
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }

        return true;
    }

    public function getHighestOrderFromParent(int $parentId, string $table, string $parentIdName)
    {
        try {
            $result = DB::table($table)
                ->select('order')
                ->where($parentIdName, $parentId)
                ->orderByDesc('order')
                ->first();

            if ($result == null) {
                return 0;
            }
        } catch (\Exception $e) {
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

    /**
     * @param $data
     * @param WorkFunction|Folder|null $parent
     * @return Document
     */
    private function makeDocument($data, $parent = null): Document
    {
        $document = new Document(
            $data->id,
            $data->originalName,
            $data->name,
            $data->content,
            $data->fromTemplate
        );

        if($parent !== null) {
            $document->setOrder($this->getOrderFromParent($document, $parent));
        }
        return $document;
    }

    /**
     * @param Document $document
     * @param WorkFunction|Folder|Company $parent
     * @return int
     */
    private function getOrderFromParent(Document $document, $parent): int
    {
        if ($parent instanceof Folder) {
            $parentIdName = 'folderId';
            $table = self::DOCUMENT_LINK_FOLDER_TABLE;
        } elseif($parent instanceof WorkFunction) {
            $parentIdName = 'workFunctionId';
            $table = WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE;
        } else {
            $parentIdName = 'companyId';
            $table = CompaniesHandler::TABLE_LINK_DOCUMENT;
        }

        try {
            $result = DB::table($table)
                ->select('order')
                ->where($parentIdName, $parent->getId())
                ->where('documentId', $document->getId())
                ->first();

            if ($result == null) {
                return 0;
            }
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return $result->order;
    }

    /**
     * Set the linked folders id to the document result. So that we can put it in the document model.
     * @param $documentResult
     * @return \Illuminate\Database\Eloquent\Model | object
     */
    private function setFoldersId($documentResult)
    {
        try {
            $foldersId = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->select('folderId')
                ->where('documentId', '=', $documentResult->id)
                ->get();
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }

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
