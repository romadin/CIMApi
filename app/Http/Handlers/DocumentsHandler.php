<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 29-1-2019
 * Time: 20:21
 */

namespace App\Http\Handlers;

use App\Models\Document\Document;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DocumentsHandler
{
    const DOCUMENT_TABLE = 'documents';
    const DOCUMENT_IMAGE_TABLE = 'document_image';
    const DOCUMENT_LINK_FOLDER_TABLE = 'folders_has_documents';
    const FOLDER_LINK_SUB_FOLDER_TABLE = 'folders_has_folders';

    //@todo need a better way for templating
    const defaultDocumentTemplate = [
        ['name' => 'Projectgegevens', 'folderName' => 'projectData', 'order' => 1, 'fromTemplate' => true],
        ['name' => 'Verplichtingen van de Opdrachtgever', 'folderName' => 'obligationsClient', 'order' => 7, 'fromTemplate' => true],
        ['name' => 'Verplichtingen van de Opdrachtnemer', 'folderName' => 'obligationsContractor', 'order' => 8, 'fromTemplate' => true],
        ['name' => 'Intellectuele eigendom', 'folderName' => 'intellectualOwnership', 'order' => 9, 'fromTemplate' => true],
        ['name' => 'Eigendom van het BIM', 'order' => 10, 'fromTemplate' => true, 'folderName' => 'propertyOfBIM'],
        ['name' => 'Aansprakelijkheid voor BIM-data', 'order' => 11, 'fromTemplate' => true, 'folderName' => 'liabilityForBIMData'],

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

    public function createDocument(Request $request)
    {
        $row = [
            'originalName' => $request->input('name'),
            'name' => $request->input('name'),
            'content' => $request->input('content'),
        ];
        try {
            $id = DB::table(self::DOCUMENT_TABLE)
                ->insertGetId($row);


            $order = $request->input('order');

            // insert the link folder has document en set order.
            DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->insert([
                    'folderId' => $request->input('folderId'),
                    'documentId' => $id,
                    'order' => isset($order) ? $order : $this->getLatestOrderFromFolder($request->input('folderId')) + 1,
                ]);

            $document = $this->getDocumentById($id);
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
        return $document;
    }

    /**
     * Create document from an given template.
     * @param int $folderId
     * @param array | string $template
     * @return Document[]
     */
    public function createDocumentsWithTemplate(int $folderId, $template)
    {
        $template = $template !== 'default' ? $template : self::defaultDocumentTemplate;
        foreach ($template as $documentTemplate) {
            $filePath = 'templateText/' .  $documentTemplate['folderName'] .'.html';
            try {
                $content = File::get(storage_path($filePath));
            } catch (\Exception $e) {
                $content = null;
            }

            $row = [
                'originalName' => $documentTemplate['name'],
                'name' => null,
                'content' => $content,
                'fromTemplate' => $documentTemplate['fromTemplate'],
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

            $document = $this->getDocumentById($id);
        } catch (\Exception $e) {
            return response('DocumentHandler: There is something wrong with the database connection', 500);
        }
        return $document;
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

    public function getLatestOrderFromFolder(int $folderId)
    {
        try {
            $query = DB::table(self::DOCUMENT_LINK_FOLDER_TABLE)
                ->select('order')
                ->where('folderId', $folderId);

            $result = DB::table(self::FOLDER_LINK_SUB_FOLDER_TABLE)
                ->select('order')
                ->where('folderId', $folderId)
                ->union($query)
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

    private function makeDocument($data): Document
    {
        $foldersId = is_array($data->folderId) ? $data->folderId : [$data->folderId];

        $document = new Document(
            $data->id,
            $data->originalName,
            $data->name,
            $data->content,
            $foldersId,
            $data->order,
            $data->fromTemplate
        );

        return $document;
    }

    private function makeDocumentImage($data)
    {

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
