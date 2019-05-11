<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 18:58
 */

namespace App\Http\Handlers;


use App\Models\Chapter\Chapter;
use Exception;
use Illuminate\Support\Facades\DB;

class ChaptersHandler
{
    const TABLE = 'chapters';

    public function getChapter(int $id, int $workFunctionId = null)
    {
        try {
            $results = DB::table(self::TABLE)
                ->where('id', $id)
                ->first();
            if ( $results === null ) {
                return response('Chapter does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('ChaptersHandler: There is something wrong with the database connection',500);
        }

        try {
            $chapter = $this->makeChapter($results, $workFunctionId);
        }catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }

        return $chapter;
    }


    public function postChapters(array $chapters, int $workFunctionId = null)
    {
        $container = [];
        foreach ($chapters as $chapter) {
            try {
                $id = DB::table(self::TABLE)
                    ->insertGetId($chapter);
            } catch (\Exception $e) {
                return \response($e->getMessage(),500);
            }
            array_push($container, $this->getChapter($id, $workFunctionId));
        }
        return $container;
    }
    /**
     * @param Chapter $headline
     * @param int $workFunctionId
     * @return int
     * @throws Exception
     */
    private function getOrder(Chapter $headline, int $workFunctionId): int
    {
        try {
            $results = DB::table(WorkFunctionsHandler::MAIN_HAS_CHAPTER_TABLE)
                ->where('workFunctionId', $workFunctionId)
                ->where('chapterId', $headline->getId())
                ->first();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $results ? $results->order : 0;
    }

    /**
     * @param \stdClass $data
     * @param int|null $workFunctionId
     * @return Chapter
     * @throws Exception
     */
    private function makeChapter(\stdClass $data, int $workFunctionId = null): Chapter
    {
        $chapter = new Chapter();
        $chapter->setId($data->id);
        $chapter->setName($data->name);
        $chapter->setContent($data->content);
        $chapter->setHeadlineId($data->headlineId);
        try {
            $chapter->setOrder($data->order ?: $this->getOrder($chapter, $workFunctionId));
        }catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
        return $chapter;
    }
}