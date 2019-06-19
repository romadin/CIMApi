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
use App\Models\Headline\Headline;
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Support\Facades\DB;

class WorkFunctionsHandler
{
    const MAIN_TABLE = 'work_functions';
    const MAIN_HAS_HEADLINE_TABLE = 'work_function_has_headline';
    const MAIN_HAS_CHAPTER_TABLE = 'work_function_has_chapter';
    const MAIN_HAS_FOLDER_TABLE = 'work_function_has_folder';
    const MAIN_HAS_DOCUMENT_TABLE = 'work_function_has_document';
    /**
     * @var HeadlinesHandler
     */
    private $headlinesHandler;
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;
    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;
    /**
     * @var FoldersHandler
     */
    private $foldersHandler;

    public function __construct(
        HeadlinesHandler $headlinesHandler,
        ChaptersHandler $chaptersHandler,
        DocumentsHandler $documentsHandler,
        FoldersHandler $foldersHandler)
    {
        $this->headlinesHandler = $headlinesHandler;
        $this->chaptersHandler = $chaptersHandler;
        $this->documentsHandler = $documentsHandler;
        $this->foldersHandler = $foldersHandler;
    }

    public function getWorkFunctionsFromTemplateId(int $parentId, string $parentIdName)
    {
        try {
            $results = DB::table(self::MAIN_TABLE)
                ->where($parentIdName, $parentId)
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
            } catch (\Exception $e) {
                return \response($e->getMessage(),500);
            }
            $workFunction = $this->getWorkFunction($id);

            /** if it is the main function we need to create and add headlines, chapters. */
            if ($workFunction->isMainFunction()) {
                $headlines = $this->headlinesHandler->postHeadlines($workFunction->getId(), TemplateDefault::HEADLINES_DEFAULT);
                $chapters = $this->chaptersHandler->postChapters(TemplateDefault::CHAPTERS_DEFAULT, $workFunction->getId());

                try {
                    $this->createWorkFunctionHasHeadlines($workFunction, $headlines);
                    $this->createWorkFunctionHasChapters($workFunction, $chapters);
                } catch (\Exception $e) {
                    return \response($e->getMessage(),500);
                }
                $workFunction->setHeadlines($headlines);
                $workFunction->setChapters($chapters);
            }
            array_push($container, $workFunction);
        }
        return $container;
    }

    /**
     * Create a new work function to a template
     * @param array $postData
     * @return WorkFunction|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function postWorkFunction(array $postData)
    {
        $postData['order'] = $this->getHighestOrder(isset($postData['templateId']) ? $postData['templateId'] : $postData['projectId']) + 1;
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
     * @param int[] $chaptersId
     * @throws Exception
     */
    public function addChapters(WorkFunction $workFunction, $chaptersId): void
    {
        $linkTable = self::MAIN_HAS_CHAPTER_TABLE;
        foreach ($chaptersId as $chapterId) {
            $row = [
                'chapterId' => $chapterId,
                'workFunctionId' => $workFunction->getId(),
                'order' => $this->getHighestOrderOfChildItems($workFunction->getId(), $linkTable, self::getLinkTableSibling($linkTable)) + 1
            ];
            try {
                $isEmpty = DB::table($linkTable)
                    ->where('chapterId', $chapterId)
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
                'order' => $this->getHighestOrderOfChildItems($workFunction->getId(), $linkTable, $this->getLinkTableSibling($linkTable)) + 1
            ];
            try {
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

    public function deleteWorkFunction(WorkFunction $workFunction)
    {
        $documents = $this->documentsHandler->getDocumentsFromWorkFunction($workFunction);
        $folders = $this->foldersHandler->getFoldersByWorkFunction($workFunction);
        try {
            $this->deleteLinks($workFunction);
        } catch (Exception $e) {
            return response($e->getMessage(),500);
        }

        if ($workFunction->isMainFunction() && $workFunction->getProjectId() !== null) {
            foreach ($documents as $document) {
                $this->documentsHandler->deleteDocument($document->getId());
            }
            $this->foldersHandler->deleteFolders($folders);
        } else {
            try {
                $this->deleteChaptersAndHeadline($workFunction);
            } catch (Exception $e) {
                return response($e->getMessage(),500);
            }
        }

        try {
            DB::table(self::MAIN_TABLE)
                ->where('id', $workFunction->getId())
                ->delete();
        } catch (Exception $e) {
            return response($e->getMessage(),500);
        }

        return json_decode('WorkFunction deleted');
    }

    /**
     * Update the order of the items from the work function.
     * Example ( order 1 going to be order 8. So we take all the orders between 1 and 8 except the one that is going to get a new order and we subtract 1 )
     * @param int $order
     * @param Headline|Chapter $childItem
     * @param int $workFunctionId
     * @return Headline|Chapter
     */
    public function updateChildOrder(int $order, $childItem, int $workFunctionId)
    {
        if ($order !== $childItem->getOrder()) {
            $inBetween = $order > $childItem->getOrder() ? [$childItem->getOrder(), $order] : [$order, $childItem->getOrder()];

            $headlinesLink = DB::table(self::MAIN_HAS_HEADLINE_TABLE)
                ->select('headlineId', 'order')
                ->where('workFunctionId', $workFunctionId)
                ->whereBetween('order', $inBetween)
                ->get()->toArray();

            $chaptersLink = DB::table(self::MAIN_HAS_CHAPTER_TABLE)
                ->select('chapterId', 'order')
                ->where('workFunctionId', $workFunctionId)
                ->whereBetween('order', $inBetween)
                ->get()->toArray();

            // filter the current child item out of the result
            $container = array_merge($headlinesLink, $chaptersLink);
            $container = array_filter($container, function($item) use ($childItem) { return $item->order !== $childItem->getOrder(); });

            foreach ($container as $item) {
                $key = isset($item->chapterId) ? 'chapterId' : 'headlineId';
                $tableName = 'work_function_has_' . str_replace('Id', '', $key);
                $item->order = $order > $childItem->getOrder() ? $item->order -1 : $item->order +1;
                DB::table($tableName)
                    ->where('workFunctionId', $workFunctionId)
                    ->where( $key, $item->$key)
                    ->update((array)$item);
            }

            $className = substr(get_class($childItem), strrpos(get_class($childItem), '\\') + 1);
            DB::table($childItem instanceof Headline ? self::MAIN_HAS_HEADLINE_TABLE : self::MAIN_HAS_CHAPTER_TABLE)
                ->where('workFunctionId', $workFunctionId)
                ->where( strtolower($className) . 'Id', $childItem->getId())
                ->update(['order' => $order]);

            $childItem->setOrder($order);
        }

        return $childItem;
    }

    /**
     * Get the highest order of all the items in the workFunction
     * @param int $workFunctionId
     * @param string $linkTable
     * @param string $linkTableSibling
     * @return int
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
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return $result->order;
    }

    /**
     * @param WorkFunction $workFunction
     * @param Headline[] $headlines
     * @param int[]|null $order
     * @throws Exception
     */
    public function createWorkFunctionHasHeadlines(WorkFunction $workFunction, $headlines, $order = null): void
    {
        try {
            foreach ($headlines as $i => $headline) {
                $row = [
                    'workFunctionId' => $workFunction->getId(),
                    'headlineId' => $headline->getId(),
                    'order' => $order ? $order[$i] : TemplateDefault::WORK_FUNCTION_HAS_HEADLINE_ORDER_DEFAULT[$i]
                ];
                DB::table(self::MAIN_HAS_HEADLINE_TABLE)
                    ->insert($row);
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
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
     * @param int $templateId
     * @return int
     */
    private function getHighestOrder(int $templateId): int
    {
        $result = DB::table(self::MAIN_TABLE)
            ->select('order')
            ->where('templateId', $templateId)
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
            DB::table(self::MAIN_HAS_FOLDER_TABLE)
                ->where('workFunctionId', $workFunction->getId())
                ->delete();
            DB::table(self::MAIN_HAS_CHAPTER_TABLE)
                ->where('workFunctionId', $workFunction->getId())
                ->delete();
            DB::table(self::MAIN_HAS_HEADLINE_TABLE)
                ->where('workFunctionId', $workFunction->getId())
                ->delete();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
    }

    /**
     * Delete the chapters and the headline
     * @param WorkFunction $workFunction
     * @throws Exception
     */
    private function deleteChaptersAndHeadline(WorkFunction $workFunction): void
    {
        try {
            foreach ($workFunction->getChapters() as $chapter) {
                $this->chaptersHandler->deleteChapterAndLink($chapter, $workFunction);
            }
            foreach ($workFunction->getHeadlines() as $headline) {
                $this->headlinesHandler->deleteHeadline($headline, $workFunction);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
    }

    private function makeWorkFunction($data): WorkFunction
    {
        $workFunction = new WorkFunction();

        $workFunction->setId($data->id);
        $workFunction->setName($data->name);
        $workFunction->setMainFunction($data->isMainFunction);
        $workFunction->setTemplateId($data->templateId);
        $workFunction->setOrder($data->order);
        $workFunction->setHeadlines($this->headlinesHandler->getHeadlinesByWorkFunction($workFunction));
        $workFunction->setChapters($this->chaptersHandler->getChaptersByParentWorkFunction($workFunction));
        $workFunction->setOn($data->on);
        $workFunction->setFromTemplate($data->fromTemplate);

        return $workFunction;
    }

    static function getLinkTableSibling($linkTable): string {
        switch ($linkTable) {
            case (self::MAIN_HAS_FOLDER_TABLE):
                return self::MAIN_HAS_DOCUMENT_TABLE;
            case (self::MAIN_HAS_DOCUMENT_TABLE):
                return self::MAIN_HAS_FOLDER_TABLE;
            case (self::MAIN_HAS_HEADLINE_TABLE):
                return self::MAIN_HAS_CHAPTER_TABLE;
            case (self::MAIN_HAS_CHAPTER_TABLE):
                return self::MAIN_HAS_HEADLINE_TABLE;
        }

        return self::MAIN_TABLE;
    }

}