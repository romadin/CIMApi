<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 18:58
 */

namespace App\Http\Handlers;


use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Chapter\Chapter;
use App\Models\Headline\Headline;
use App\Models\WorkFunction\WorkFunction;

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

    /**
     * Get the chapters from the parent headline.
     * @param Headline $headline
     * @return Chapter[]|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getChaptersByParentHeadline(Headline $headline)
    {
        try {
            $results = DB::table(self::TABLE)
                ->where('headlineId', $headline->getId())
                ->get();
            if ( $results === null ) {
                return [];
            }
        } catch (\Exception $e) {
            return \response('ChaptersHandler: There is something wrong with the database connection',500);
        }

        $container = [];
        try {
            foreach ($results as $result) {
                $chapter = $this->makeChapter($result);
                array_push($container, $chapter);
            }
        }catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }

        return $container;
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
     * Check if we need to delete the chapter or only the connections.
     * @param Chapter $chapter
     * @param WorkFunction|null $workFunction
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     */
    public function deleteChapterAndLink(Chapter $chapter, WorkFunction $workFunction = null)
    {
        if ($workFunction) {
            $this->deleteWorkFunctionHasChapter($chapter->getId(), $workFunction->getId());
            if ($workFunction->isMainFunction()) {
                $links = DB::table(WorkFunctionsHandler::MAIN_HAS_CHAPTER_TABLE)
                    ->where('chapterId', $chapter->getId())
                    ->get();
                foreach ($links as $link) {
                    $this->deleteWorkFunctionHasChapter($chapter->getId(), $link->chapterId);
                }
                return $this->deleteChapter($chapter);
            }
            return json_decode('Chapter link deleted');
        }
        return $this->deleteChapter($chapter);
    }

    /**
     * Delete chapter
     * @param Chapter $chapter
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     */
    private function deleteChapter(Chapter $chapter)
    {
        try {
            DB::table(self::TABLE)
                ->where('id', $chapter->getId())
                ->delete();
        } catch (Exception $e) {
            return \response($e->getMessage(),500);
        }
        return json_decode('Chapter deleted');
    }

    /**
     * Delete the connection between the chapter and work function.
     * @param int $chapterId
     * @param int $workFunctionId
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     */
    private function deleteWorkFunctionHasChapter(int $chapterId, int $workFunctionId)
    {
        try {
            DB::table(WorkFunctionsHandler::MAIN_HAS_CHAPTER_TABLE)
                ->where('chapterId', $chapterId)
                ->where('workFunctionId', $workFunctionId)
                ->delete();
        } catch (Exception $e) {
            return \response($e->getMessage(),500);
        }
        return json_decode('Chapter link deleted');
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