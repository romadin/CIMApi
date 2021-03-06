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
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DocumentsHandler
{
    const DOCUMENT_TABLE = 'documents';
    const DOCUMENT_IMAGE_TABLE = 'document_image';
    const DOCUMENT_LINK_DOCUMENT_TABLE = 'documents_has_documents';
    const DOCUMENT_LINK_COMPANY_WORK_FUNCTION = 'work_function_has_companies_has_documents';

    /**
     * @param WorkFunction $workFunction
     * @return Document[]
     * @throws Exception
     */
    public function getDocumentsFromWorkFunction(WorkFunction $workFunction)
    {
        try {
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
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $documents;
    }

    public function getDocumentsFromCompany(Company $company, WorkFunction $workFunction)
    {
        $linkTable = self::DOCUMENT_LINK_COMPANY_WORK_FUNCTION;
        $documentsResult = DB::table(self::DOCUMENT_TABLE)
            ->select([
                self::DOCUMENT_TABLE.'.id', self::DOCUMENT_TABLE.'.originalName',
                self::DOCUMENT_TABLE.'.name', self::DOCUMENT_TABLE.'.content',
                self::DOCUMENT_TABLE.'.fromTemplate',
                $linkTable.'.order',
            ])
            ->where($linkTable.'.companyId', '=', $company->getId())
            ->where($linkTable.'.workFunctionId', '=', $workFunction->getId())
            ->join($linkTable, $linkTable. '.documentId', '=', self::DOCUMENT_TABLE. '.id')
            ->get();

        $documents = [];

        forEach ( $documentsResult as $document ) {
            $document->parentId = $company->getId();
            array_push($documents, $this->makeDocument($document, ['company' => $company, 'workFunction' => $workFunction]));
        }

        return $documents;
    }

    public function getSubDocuments(Document $document)
    {
        $linkTable = self::DOCUMENT_LINK_DOCUMENT_TABLE;

        try {
            $documentsResult = DB::table(self::DOCUMENT_TABLE)
                ->select([
                    self::DOCUMENT_TABLE.'.id', self::DOCUMENT_TABLE.'.originalName',
                    self::DOCUMENT_TABLE.'.name', self::DOCUMENT_TABLE.'.content',
                    self::DOCUMENT_TABLE.'.fromTemplate',
                    $linkTable.'.order',
                ])
                ->where($linkTable.'.documentId', '=', $document->getId())
                ->join($linkTable, $linkTable. '.subDocumentId', '=', self::DOCUMENT_TABLE. '.id')
                ->get();

            $documents = [];

            forEach ( $documentsResult as $subDocument ) {
                array_push($documents, $this->makeDocument($subDocument, $document, true));
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $documents;
    }

    /**
     * @param int $id
     * @param WorkFunction|null $parent
     * @return Document|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws Exception
     */
    public function getDocumentById(int $id, $parent = null)
    {
        try {
            $documentResult = DB::table(self::DOCUMENT_TABLE)
                ->where(self::DOCUMENT_TABLE.'.id', '=', $id)
                ->first();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
        return $this->makeDocument($documentResult, $parent);
    }

    /**
     * @param array $postData
     * @return Document|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws Exception
     */
    public function postDocument(array $postData)
    {
        $postData['originalName'] =  isset($postData['originalName']) ? $postData['originalName'] : $postData['name'];

        try {
            $id = DB::table(self::DOCUMENT_TABLE)
                ->insertGetId($postData);

            $document = $this->getDocumentById($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $document;
    }

    /**
     * Insert the link document with parent document|workFunction en set order.
     * @param array $child
     * @param array $parent
     * @param string $linkTable
     * @param int|null $order
     * @throws Exception
     */
    public function setDocumentLink(array $parent, array $child, string $linkTable, ?int $order = null)
    {
        try {
            DB::table($linkTable)
                ->insert([
                    $parent['name'] => $parent['id'],
                    $child['name'] => $child['id'],
                    'order' => isset($order) ? $order : $this->getHighestOrderFromParent($parent['id'], $linkTable, $parent['name']) + 1,
                ]);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
    }

    /**
     * Create document from an given template.
     * @param Document|WorkFunction $parentItem
     * @param Chapter[] $documents
     * @param string $linkTable
     * @return Document[]
     * @throws Exception
     */
    public function createDocumentsWithTemplate($parentItem, $documents, string $linkTable)
    {
        try {
            foreach ($documents as $document) {
                $row = [
                    'originalName' => $document->getName(),
                    'name' => null,
                    'content' => $document->getContent(),
                    'fromTemplate' => true,
                ];
                $newDocument = $this->postDocument($row);

                $parentLinkTable = ['id' => $parentItem->getId()];

                if ($parentItem instanceof Document) {
                    $parentLinkTable['name'] = 'documentId';
                    $childLink = ['name' => 'subDocumentId', 'id' => $newDocument->getId()];
                } else {
                    $childLink = ['name' => 'documentId', 'id' => $newDocument->getId()];
                    $parentLinkTable['name'] = 'workFunctionId';
                }

                $this->setDocumentLink($parentLinkTable, $childLink, $linkTable, $document->getOrder());
                if (!empty($document->getChapters())) {
                    $this->createDocumentsWithTemplate($newDocument, $document->getChapters(), self::DOCUMENT_LINK_DOCUMENT_TABLE);
                }
            }
        }catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        if($parentItem instanceof Document) {
            return $this->getSubDocuments($parentItem);
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
            return response($e->getMessage(), 500);
        }
        return $document;
    }

    /**
     * Only DELETE the link between the document and the given parent. Real document is still saved.
     * @param string $linkTable
     * @param array $where
     * @param int $documentId
     * @return mixed
     * @throws Exception
     */
    public function deleteDocumentLink(string $linkTable, array $where, int $documentId)
    {
        try {
            DB::table($linkTable)
                ->where($where)
                ->where('documentId', $documentId)
                ->delete();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return json_encode('Document link deleted');
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteDocument(int $id)
    {
        try {
            // get subDocuments id.
            $results = DB::table(self::DOCUMENT_LINK_DOCUMENT_TABLE)
                ->select('subDocumentId')
                ->where('documentId', $id)
                ->get();

            // delete link with sub documents
            DB::table(self::DOCUMENT_LINK_DOCUMENT_TABLE)
                ->where('documentId', $id)
                ->delete();

            // delete subDocuments
            foreach ($results as $result) {
                DB::table(self::DOCUMENT_TABLE )
                    ->where('id', $result->subDocumentId)
                    ->delete();
            }

            // delete link with parent work function.
            DB::table(WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE)
                ->where('documentId', $id)
                ->delete();

            DB::table(self::DOCUMENT_TABLE)->delete($id);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 500);
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
     * @param Document|WorkFunction|array $parent
     * @param bool $isSub
     * @return Document
     * @throws Exception
     */
    private function makeDocument($data, $parent = null, $isSub = false): Document
    {
        $document = new Document();

        try {
            foreach ($data as $key => $value) {
                if ($value) {
                    $method = 'set'. ucfirst($key);
                    if(method_exists($document, $method)) {
                        $document->$method($value);
                    }
                }
            }
            if ($parent) {
                $document->setOrder(is_array($parent) ? $this->getOrderFromCompanyAndWorkFunction($parent, $document) : $this->getOrderFromParent($document, $parent));
            }
            if (!$isSub) {
                $document->setSubDocuments($this->getSubDocuments($document));
            }
        } catch(Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $document;
    }

    /**
     * @param Document $document
     * @param WorkFunction|Document $parent
     * @return int
     */
    private function getOrderFromParent(Document $document, $parent): int
    {
        $linkIdName = 'documentId';
        if ($parent instanceof Document) {
            $parentIdName = 'documentId';
            $table = self::DOCUMENT_LINK_DOCUMENT_TABLE;
            $linkIdName = 'subDocumentId';
        } else {
            $parentIdName = 'workFunctionId';
            $table = WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE;
        }

        try {
            $result = DB::table($table)
                ->select('order')
                ->where($parentIdName, $parent->getId())
                ->where($linkIdName, $document->getId())
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
     * @param array $parentContainer
     * @param Document $document
     * @return int
     * @throws Exception
     */
    private function getOrderFromCompanyAndWorkFunction(array $parentContainer, Document $document): int
    {
        $company = $parentContainer['company'];
        $workFunction = $parentContainer['workFunction'];
        try {
            $result = DB::table(self::DOCUMENT_LINK_COMPANY_WORK_FUNCTION)
                ->select('order')
                ->where('companyId', $company->getId())
                ->where('workFunctionId', $workFunction->getId())
                ->where('documentId', $document->getId())
                ->first();

            if ($result == null) {
                return 0;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $result->order;
    }

}
