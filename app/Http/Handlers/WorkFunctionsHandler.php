<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 16:25
 */

namespace App\Http\Handlers;


use App\Http\Controllers\Templates\TemplateDefault;
use App\Models\Chapter\Chapter;
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Support\Facades\DB;

class WorkFunctionsHandler
{
    const MAIN_TABLE = 'work_functions';
    const MAIN_HAS_CHAPTER_TABLE = 'work_function_has_chapter';
    const MAIN_HAS_DOCUMENT_TABLE = 'work_function_has_document';
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;
    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;

    /**
     * @var CompaniesHandler
     */
    private $companiesHandler;

    public function __construct(
        ChaptersHandler $chaptersHandler,
        DocumentsHandler $documentsHandler,
        CompaniesHandler $companiesHandler)
    {
        $this->chaptersHandler = $chaptersHandler;
        $this->documentsHandler = $documentsHandler;
        $this->companiesHandler = $companiesHandler;
    }

    public function getWorkFunctionsFromTemplateId(int $parentId, string $parentIdName)
    {
        try {
            $results = DB::table(self::MAIN_TABLE)
                ->where($parentIdName, $parentId)
                ->get();

            $workFunctions = [];
            foreach ($results as $result) {
                array_push($workFunctions, $this->makeWorkFunction($result));
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }


        return $workFunctions;
    }

    public function getWorkFunctionsFromProjectId(int $projectId)
    {
        try {
            $results = DB::table(self::MAIN_TABLE)
                ->where('projectId', $projectId)
                ->get();
        } catch (\Exception $e) {
            return \response('WorkFunctionsHandler: There is something wrong with the database connection',500);
        }

        $workFunctions = [];
        foreach ($results as $result) {
            array_push($workFunctions, $this->makeWorkFunction($result));
        }

        return $workFunctions;
    }

    public function getWorkFunction(int $id)
    {
        try {
            $results = DB::table(self::MAIN_TABLE)
                ->where('id', $id)
                ->first();
            if ( $results === null ) {
                return response('Work Function does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('WorkFunctionsHandler: There is something wrong with the database connection',500);
        }

        return $this->makeWorkFunction($results);
    }

    /**
     * @param int $templateId
     * @param WorkFunction[] $workFunctions
     * @return WorkFunction[]
     * @throws Exception
     */
    public function postWorkFunctions(int $templateId, array $workFunctions)
    {
        $container = [];
        foreach ($workFunctions as $index => $workFunction) {
            $row = [
                'name' => $workFunction['name'],
                'isMainFunction' => isset($workFunction['isMainFunction']) ? $workFunction['isMainFunction'] : false,
                'order' => isset($workFunction['order']) ? $workFunction['order'] : $index,
                'templateId' => $templateId,
            ];
            try {
                $id = DB::table(self::MAIN_TABLE)
                    ->insertGetId($row);
            } catch (Exception $e) {
                throw new Exception($e->getMessage(),500);
            }
            $workFunction = $this->getWorkFunction($id);

            /** if it is the main function we need to create and add chapters. */
            if ($workFunction->isMainFunction()) {
                $chapters = $this->chaptersHandler->postChapters(TemplateDefault::CHAPTERS_DEFAULT, $workFunction->getId());

                try {
                    $this->createWorkFunctionHasChapters($workFunction, $chapters);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage(),500);
                }
                $workFunction->setChapters($chapters);
            }
            array_push($container, $workFunction);
        }

        return $container;
    }

    /**
     * Create a new work function to a template
     * @param array $postData
     * @param string $parentIdName
     * @return WorkFunction|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function postWorkFunction(array $postData, string $parentIdName)
    {
        $id = isset($postData['templateId']) ? $postData['templateId'] : $postData['projectId'];
        $postData['order'] = $this->getHighestOrder(self::MAIN_TABLE, $parentIdName, $id) + 1;
        try {
            $id = DB::table(self::MAIN_TABLE)
                ->insertGetId($postData);
        } catch (Exception $e) {
            return \response($e->getMessage(),500);
        }

        return $this->getWorkFunction($id);
    }

    public function editWorkFunction(array $postData, int $id)
    {
        if(!empty($postData)) {
            try {
                DB::table(self::MAIN_TABLE)
                    ->where('id', $id)
                    ->update($postData);
            } catch (Exception $e) {
                return \response($e->getMessage(),500);
            }
        }

        return $this->getWorkFunction($id);
    }

    /**
     * @param WorkFunction $workFunction
     * @param int[] $itemsId
     * @param string $itemIdName
     * @param string $linkTable
     * @throws Exception
     */
    public function addChildItems(WorkFunction $workFunction, $itemsId, string $itemIdName, string $linkTable): void
    {
        foreach ($itemsId as $itemId) {
            $row = [
                $itemIdName => $itemId,
                'workFunctionId' => $workFunction->getId(),
            ];

            try {
                $row['order'] = $this->getHighestOrder($linkTable,'workFunctionId', $workFunction->getId()) + 1;

                $isEmpty = DB::table($linkTable)
                    ->where($itemIdName, $itemId)
                    ->where('workFunctionId', $workFunction->getId())
                    ->get()->isEmpty();

                if($isEmpty) {
                    DB::table($linkTable)
                        ->insert($row);
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage(),500);
            }
        }
    }

    public function reOrderWorkFunctions(WorkFunction $workFunction, int $order)
    {
        if ($order !== $workFunction->getOrder()) {
            $inBetween = $order > $workFunction->getOrder() ? [$workFunction->getOrder(), $order] : [$order, $workFunction->getOrder()];

            $workFunctions = DB::table(self::MAIN_TABLE)
                ->select('id', 'order')
                ->where('templateId', $workFunction->getTemplateId())
                ->where('id', '!=', $workFunction->getId())
                ->whereBetween('order', $inBetween)
                ->get()->toArray();

            foreach ($workFunctions as $item) {
                $item->order = $order > $workFunction->getOrder() ? $item->order -1 : $item->order +1;
                DB::table(self::MAIN_TABLE)
                    ->where('id', $item->id)
                    ->update((array)$item);
            }

            DB::table(self::MAIN_TABLE)
                ->where('id', $workFunction->getId())
                ->update(['order' => $order]);
            $workFunction->setOrder($order);
        }

        return $workFunction;
    }

    /**
     * We remove the main function boolean.
     * @param WorkFunction $workFunction
     * @return WorkFunction
     */
    public function removeMainFunction(WorkFunction $workFunction): WorkFunction
    {
        DB::table(self::MAIN_TABLE)
            ->where('templateId', $workFunction->getTemplateId())
            ->where('isMainFunction', '=', 1)
            ->update(['isMainFunction' => 0]);
        return $workFunction;
    }

    /**
     * @param WorkFunction $workFunction
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     * @throws Exception
     */
    public function deleteWorkFunction(WorkFunction $workFunction)
    {
        $documents = $this->documentsHandler->getDocumentsFromWorkFunction($workFunction);
        try {
            $this->deleteLinks($workFunction);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        try {
            if ($workFunction->isMainFunction() && $workFunction->getProjectId() !== null) {
                foreach ($documents as $document) {
                    $this->documentsHandler->deleteDocument($document->getId());
                }
            } else {
                $this->deleteChapters($workFunction);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        try {
            DB::table(self::MAIN_TABLE)
                ->where('id', $workFunction->getId())
                ->delete();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return json_decode('WorkFunction deleted');
    }

    /**
     * Update the order of the items from the work function.
     * Example ( order 1 going to be order 8. So we take all the orders between 1 and 8 except the one that is going to get a new order and we subtract 1 )
     * @param int $order
     * @param Chapter $chapter
     * @param int $workFunctionId
     * @return Chapter
     */
    public function updateChildOrder(int $order, Chapter $chapter, int $workFunctionId)
    {
        if ($order !== $chapter->getOrder()) {
            $inBetween = $order > $chapter->getOrder() ? [$chapter->getOrder(), $order] : [$order, $chapter->getOrder()];

            $chaptersLink = DB::table(self::MAIN_HAS_CHAPTER_TABLE)
                ->select('chapterId', 'order')
                ->where('workFunctionId', $workFunctionId)
                ->whereBetween('order', $inBetween)
                ->get()->toArray();

            // filter the current child item out of the result
            $chaptersLink = array_filter($chaptersLink, function($item) use ($chapter) { return $item->order !== $chapter->getOrder(); });

            foreach ($chaptersLink as $chapterLink) {
                $chapterLink->order = $order > $chapter->getOrder() ? $chapterLink->order -1 : $chapterLink->order +1;
                DB::table(self::MAIN_HAS_CHAPTER_TABLE)
                    ->where('workFunctionId', $workFunctionId)
                    ->where( 'chapterId', $chapterLink->chapterId)
                    ->update((array)$chapterLink);
            }

            DB::table(self::MAIN_HAS_CHAPTER_TABLE)
                ->where('workFunctionId', $workFunctionId)
                ->where('chapterId', $chapter->getId())
                ->update(['order' => $order]);

            $chapter->setOrder($order);
        }

        return $chapter;
    }

    /**
     * Get the highest order of all the items in the workFunction
     * @param int $workFunctionId
     * @param string $linkTable
     * @param string $linkTableSibling
     * @return int
     * @throws Exception
     */
    public function getHighestOrderOfChildItems(int $workFunctionId, string $linkTable, string $linkTableSibling): int
    {
        try {
            $query = DB::table($linkTable)
                ->select('order')
                ->where('workFunctionId', $workFunctionId);

            $result = DB::table($linkTableSibling)
                ->select('order')
                ->where('workFunctionId', $workFunctionId)
                ->union($query)
                ->orderByDesc('order')
                ->first();
            if ($result == null) {
                return 0;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 403);
        }

        return $result->order;
    }

    /**
     * @param WorkFunction $workFunction
     * @param Chapter[] $chapters
     * @param int[]|null $order
     * @throws Exception
     */
    public function createWorkFunctionHasChapters(WorkFunction $workFunction, $chapters, $order = null): void
    {
        try {
            foreach ($chapters as $i => $chapter) {
                $row = [
                    'workFunctionId' => $workFunction->getId(),
                    'chapterId' => $chapter->getId(),
                    'order' => $order ? $order[$i] : TemplateDefault::WORK_FUNCTION_HAS_CHAPTER_ORDER_DEFAULT[$i]
                ];
                DB::table(self::MAIN_HAS_CHAPTER_TABLE)
                    ->insert($row);
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
    }

    /**
     * Get the highest order from the work_functions table
     * @param string $table
     * @param string $idName
     * @param int $parentId
     * @return int
     */
    public function getHighestOrder(string $table, string $idName, int $parentId): int
    {
        $result = DB::table($table)
            ->select('order')
            ->where($idName, $parentId)
            ->orderByDesc('order')
            ->first();
        if ($result == null) {
            return 0;
        }

        return $result->order;
    }

    /**
     * Delete all the links between child items and workFunction.
     * @param WorkFunction $workFunction
     * @throws Exception
     */
    private function deleteLinks(WorkFunction $workFunction): void
    {
        try {
            DB::table(self::MAIN_HAS_DOCUMENT_TABLE)
                ->where('workFunctionId', $workFunction->getId())
                ->delete();
            DB::table(self::MAIN_HAS_CHAPTER_TABLE)
                ->where('workFunctionId', $workFunction->getId())
                ->delete();
            DB::table(CompaniesHandler::TABLE_LINK_WORK_FUNCTION)
                ->where('workFunctionId', $workFunction->getId())
                ->delete();
            DB::table(DocumentsHandler::DOCUMENT_LINK_COMPANY_WORK_FUNCTION)
                ->where('workFunctionId', $workFunction->getId())
                ->delete();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
    }

    /**
     * Delete the chapters
     * @param WorkFunction $workFunction
     * @throws Exception
     */
    private function deleteChapters(WorkFunction $workFunction): void
    {
        try {
            foreach ($workFunction->getChapters() as $chapter) {
                $this->chaptersHandler->deleteChapterAndLink($chapter, $workFunction);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
    }

    /**
     * @param $data
     * @return WorkFunction
     * @throws Exception
     */
    private function makeWorkFunction($data): WorkFunction
    {
        $workFunction = new WorkFunction();

        try {
            $workFunction->setId($data->id);
            $workFunction->setName($data->name);
            $workFunction->setMainFunction($data->isMainFunction);
            $workFunction->setTemplateId($data->templateId);
            $workFunction->setProjectId($data->projectId);
            $workFunction->setOrder($data->order);
            $workFunction->setChapters($this->chaptersHandler->getChaptersByParentWorkFunction($workFunction));
            $workFunction->setOn($data->on);
            $workFunction->setFromTemplate($data->fromTemplate);
            $companies = $this->companiesHandler->getCompaniesByWorkFunction($workFunction);
            $workFunction->setCompanies($companies);
            $workFunction->setDocuments($this->documentsHandler->getDocumentsFromWorkFunction($workFunction));
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 404);
        }

        return $workFunction;
    }
}
