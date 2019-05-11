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
    /**
     * @var HeadlinesHandler
     */
    private $headlinesHandler;
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;

    public function __construct(HeadlinesHandler $headlinesHandler, ChaptersHandler $chaptersHandler)
    {
        $this->headlinesHandler = $headlinesHandler;
        $this->chaptersHandler = $chaptersHandler;
    }

    public function getWorkFunctions(int $templateId)
    {
        try {
            $results = DB::table(self::MAIN_TABLE)
                ->where('templateId', $templateId)
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
            }
            array_push($container, $workFunction);
        }
        return $container;
    }


    /**
     * @param WorkFunction $workFunction
     * @param Headline[] $headlines
     * @param int[]|null $order
     * @throws Exception
     */
    private function createWorkFunctionHasHeadlines(WorkFunction $workFunction, $headlines, $order = null): void
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
    private function createWorkFunctionHasChapters(WorkFunction $workFunction, $chapters, $order = null): void
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

    private function makeWorkFunction($data): WorkFunction
    {
        $workFunction = new WorkFunction();

        $workFunction->setId($data->id);
        $workFunction->setName($data->name);
        $workFunction->setMainFunction($data->isMainFunction);
        $workFunction->setTemplateId($data->templateId);
        $workFunction->setOrder($data->order);

        return $workFunction;
    }

}