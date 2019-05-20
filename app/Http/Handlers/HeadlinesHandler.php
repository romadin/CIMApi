<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 17:52
 */

namespace App\Http\Handlers;


use App\Http\Controllers\Templates\TemplateDefault;
use App\Models\Headline\Headline;
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Support\Facades\DB;

class HeadlinesHandler
{
    const TABLE = 'headlines';
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;

    public function __construct(ChaptersHandler $chaptersHandler)
    {
        $this->chaptersHandler = $chaptersHandler;
    }

    public function getHeadline(int $id, int $workFunctionId)
    {
        try {
            $results = DB::table(self::TABLE)
                ->where('id', $id)
                ->first();
            if ( $results === null ) {
                return response('Headline does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('HeadlinesHandler: There is something wrong with the database connection',500);
        }

        try {
            $headline = $this->makeHeadline($results, $workFunctionId);
        }catch (\Exception $e) {
            return \response('HeadlinesHandler: There is something wrong with the database connection',500);
        }

        return $headline;
    }

    /**
     * Get the headlines connected to the work function.
     * @param WorkFunction $workFunction
     * @return array|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getHeadlinesByWorkFunction(WorkFunction $workFunction)
    {
        try {
            $results = DB::table(self::TABLE)
                ->select([
                    self::TABLE.'.id',
                    self::TABLE.'.name'])
                ->join(WorkFunctionsHandler::MAIN_HAS_HEADLINE_TABLE, self::TABLE.'.id', '=',WorkFunctionsHandler::MAIN_HAS_HEADLINE_TABLE.'.headlineId')
                ->where('workFunctionId', $workFunction->getId())
                ->get();
            if ( $results === null ) {
                return [];
            }
        } catch (\Exception $e) {
            return \response('HeadlinesHandler: There is something wrong with the database connection',500);
        }

        $container = [];
        try {
            foreach ($results as $result) {
                array_push($container, $this->makeHeadline($result, $workFunction->getId()));
            }
        }catch (\Exception $e) {
            return \response('HeadlinesHandler: There is something wrong with the database connection',500);
        }

        return $container;
    }

    public function postHeadlines(int $workFunctionId, array $newHeadlines)
    {
        $container = [];
        foreach ($newHeadlines as $newHeadline) {
            try {
                $id = DB::table(self::TABLE)
                    ->insertGetId($newHeadline);
            } catch (\Exception $e) {
                return \response($e->getMessage(),500);
            }
            $headline = $this->getHeadline($id, $workFunctionId);

            // set for each default chapter the parent headline id
            $defaultChapters = array_map(function($chapterRow) use ($headline) {
                return array_merge($chapterRow, ['headlineId' => $headline->getId()]);
            }, TemplateDefault::CHAPTERS_FOR_HEADLINE[$headline->getName()]);

            $headline->setChapters($this->chaptersHandler->postChapters($defaultChapters));
            array_push($container, $headline);
        }
        return $container;
    }

    /**
     * Create a new headline.
     * @param array $postData
     * @param int $workFunctionId
     * @return Headline|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function postHeadline(array $postData, int $workFunctionId)
    {
        try {
            $id = DB::table(self::TABLE)
                ->insertGetId($postData);
        } catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }

        return $this->getHeadline($id, $workFunctionId);
    }

    /**
     * Update a headline.
     * @param array $postData
     * @param int $id
     * @param int $workFunctionId
     * @return Headline|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function updateHeadline(array $postData, int $id, int $workFunctionId)
    {
        try {
            DB::table(self::TABLE)
                ->where('id', $id)
                ->update($postData);
        } catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }

        return $this->getHeadline($id, $workFunctionId);
    }

    /**
     * Delete headline only if the work function is the main function otherwise delete the link between headline and work function.
     * @param Headline $headline
     * @param WorkFunction $workFunction
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     */
    public function deleteHeadline(Headline $headline, WorkFunction $workFunction)
    {
        $links = DB::table(WorkFunctionsHandler::MAIN_HAS_HEADLINE_TABLE)
            ->where('headlineId', $headline->getId())
            ->get();
        foreach ($links as $link) {
            $this->deleteWorkFunctionHasHeadline($headline->getId(), $link->workFunctionId);
        }

        if ($workFunction->isMainFunction()) {
            foreach ($this->chaptersHandler->getChaptersByParentHeadline($headline) as $chapter) {
                $this->chaptersHandler->deleteChapterAndLink($chapter);
            }
            try {
                // delete headline
                DB::table(self::TABLE)
                    ->where('id', $headline->getId())
                    ->delete();
            } catch (Exception $e) {
                return \response($e->getMessage(),500);
            }
            return json_decode('Headline deleted');
        }
        return json_decode('Headline link deleted');
    }

    /**
     * Delete the connection between headline and work function.
     * @param int $headlineId
     * @param int $workFunctionId
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     */
    private function deleteWorkFunctionHasHeadline(int $headlineId, int $workFunctionId)
    {
        try {
            DB::table(WorkFunctionsHandler::MAIN_HAS_HEADLINE_TABLE)
                ->where('headlineId', $headlineId)
                ->where('workFunctionId', $workFunctionId)
                ->delete();
        } catch (Exception $e) {
            return \response($e->getMessage(),500);
        }
        return json_decode('Headline link deleted');
    }

    /**
     * @param Headline $headline
     * @param int $workFunctionId
     * @return int
     * @throws Exception
     */
    private function getOrder(Headline $headline, int $workFunctionId): int
    {
        try {
            $results = DB::table(WorkFunctionsHandler::MAIN_HAS_HEADLINE_TABLE)
                ->where('workFunctionId', $workFunctionId)
                ->where('headlineId', $headline->getId())
                ->first();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $results ? $results->order : 0;
    }

    /**
     * @param \stdClass $data
     * @param int $workFunctionId
     * @return Headline
     * @throws Exception
     */
    private function makeHeadline(\stdClass $data, int $workFunctionId): Headline
    {
        $headline = new Headline();

        $headline->setId($data->id);
        try {
            $headline->setOrder($this->getOrder($headline, $workFunctionId));
        }catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
        $headline->setName($data->name);
        $headline->setChapters($this->chaptersHandler->getChaptersByParentHeadline($headline));

        return $headline;
    }
}