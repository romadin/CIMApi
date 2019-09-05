<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 18:58
 */

namespace App\Http\Handlers;

use App\Http\Controllers\Templates\TemplateDefault;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Chapter\Chapter;
use App\Models\Headline\Headline;
use App\Models\WorkFunction\WorkFunction;

class ChaptersHandler
{
    const TABLE = 'chapters';
    const CHAPTERS_HAS_CHAPTERS = self::TABLE . '_has_' . self::TABLE;

    public function getChapter(int $id, int $workFunctionId = null)
    {
        try {
            $results = DB::table(self::TABLE)
                ->where('id', $id)
                ->first();
            if ( $results === null ) {
                return [];
            }
            $chapter = $this->makeChapter($results, $workFunctionId);
        } catch (Exception $e) {
            throw new Exception ('ChaptersHandler: There is something wrong with the database connection',500);
        }

        return $chapter;
    }

    public function getSubChapters(Chapter $chapter)
    {
        try {
            $results = DB::table(self::TABLE)
                ->where('parentChapterId', $chapter->getId())
                ->get();
            if ( $results === null ) {
                return [];
            }
            $chapters = [];

            foreach ($results as $result) {
                $chapter = $this->makeChapter($result, null, true);
                array_push($chapters, $chapter);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $chapters;
    }

    /**
     * Get the chapters from the parent headline.
     * @param Headline $headline
     * @return Chapter[]|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getChaptersByParentHeadline(Headline $headline)
    {
//        try {
//            $results = DB::table(self::TABLE)
//                ->where('headlineId', $headline->getId())
//                ->get();
//            if ( $results === null ) {
//                return [];
//            }
//        } catch (\Exception $e) {
//            return \response('ChaptersHandler: There is something wrong with the database connection',500);
//        }
//
//        $container = [];
//        try {
//            foreach ($results as $result) {
//                $chapter = $this->makeChapter($result);
//                array_push($container, $chapter);
//            }
//        }catch (\Exception $e) {
//            return \response($e->getMessage(),500);
//        }
//
//        return $container;
    }

    /**
     * Get the chapters connected to the work function.
     * @param WorkFunction $workFunction
     * @return array|Chapter[]
     * @throws Exception
     */
    public function getChaptersByParentWorkFunction(WorkFunction $workFunction)
    {
        try {
            $results = DB::table(self::TABLE)
                ->select([
                    self::TABLE.'.id',
                    self::TABLE.'.name',
                    self::TABLE.'.content',
                    self::TABLE.'.parentChapterId',
                    self::TABLE.'.order'])
                ->join(WorkFunctionsHandler::MAIN_HAS_CHAPTER_TABLE, self::TABLE.'.id', '=',WorkFunctionsHandler::MAIN_HAS_CHAPTER_TABLE.'.chapterId')
                ->where('workFunctionId', $workFunction->getId())
                ->get();
            if ( $results === null ) {
                return [];
            }

            $chapters = [];
            foreach ($results as $result) {
                $chapter = $this->makeChapter($result, $workFunction->getId());
                array_push($chapters, $chapter);
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $chapters;
    }

    public function postChapters(array $chapters, int $workFunctionId = null)
    {
        $container = [];
        foreach ($chapters as $chapter) {
            try {
                $id = DB::table(self::TABLE)
                    ->insertGetId($chapter);

                if (isset(TemplateDefault::SUB_CHAPTERS[$chapter['name']])) {

                    $subChapters = TemplateDefault::SUB_CHAPTERS[$chapter['name']];
                    foreach ($subChapters as $subChapter) {
                        $subChapter['parentChapterId'] = $id;
                        $this->postChapter($subChapter);
                    }
                }

            } catch (Exception $e) {
                throw new Exception($e->getMessage(),500);
            }
            array_push($container, $this->getChapter($id, $workFunctionId));
        }
        return $container;
    }

    public function postChapter(array $postData, int $workFunctionId = null)
    {
        try {
            $id = DB::table(self::TABLE)
                ->insertGetId($postData);
        } catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }

        return $this->getChapter($id, $workFunctionId);
    }

    /**
     * Update the chapter in the database.
     * @param Chapter $chapter
     * @return Chapter
     * @throws Exception
     */
    public function updateChapter(Chapter $chapter)
    {
        $data = $chapter->jsonSerialize();
        unset($data['chapters']);

        try {
            DB::table(self::TABLE)
                ->where('id', $chapter->getId())
                ->update($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
        return $chapter;
    }

    /**
     * Reorder the chapters within the same headline.
     * @param Chapter $chapter
     * @param int $order
     */
    public function reOrderSubChapters(Chapter $chapter, int $order): void
    {
        $inBetween = $order > $chapter->getOrder() ? [$chapter->getOrder(), $order] : [$order, $chapter->getOrder()];

        $chapters = DB::table(self::TABLE)
            ->select('id', 'order')
            ->where('parentChapterId', $chapter->getParentChapterId())
            ->where('id', '!=', $chapter->getId())
            ->whereBetween('order', $inBetween)
            ->get()->toArray();

        foreach ($chapters as $item) {
            $item->order = $order > $chapter->getOrder() ? $item->order -1 : $item->order +1;
            DB::table(self::TABLE)
                ->where('id', $item->id)
                ->update((array)$item);
        }
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
            if ($this->getChaptersLinks($chapter->getId()) === 0) {
                return $this->deleteChapter($chapter);
            }
            return json_decode('Chapter link deleted');
        }
        return $this->deleteChapter($chapter);
    }

    /**
     * Get the highest order from the same headline.
     * @param int $headlineId
     * @return int
     */
    public function getHighestOrderInHeadline(int $headlineId): int
    {
        $result = DB::table(self::TABLE)
            ->select('order')
            ->where('headlineId', $headlineId)
            ->orderByDesc('order')
            ->first();
        if ($result == null) {
            return 0;
        }

        return $result->order;
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

    private function getChaptersLinks(int $chapterId)
    {
        try {
            $result = count(DB::table(WorkFunctionsHandler::MAIN_HAS_CHAPTER_TABLE)
                ->where('chapterId', $chapterId)
                ->get()->toArray());
        } catch (Exception $e) {
            return \response($e->getMessage(),500);
        }
        return $result;
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
     * @param boolean $isSubChapter
     * @return Chapter
     * @throws Exception
     */
    private function makeChapter(\stdClass $data, int $workFunctionId = null, $isSubChapter = false): Chapter
    {
        $chapter = new Chapter();
        $chapter->setId($data->id);
        $chapter->setName($data->name);
        $chapter->setContent($data->content);
        $chapter->setParentChapterId($data->parentChapterId);

        try {
            if (!$isSubChapter) {
                $chapter->setChapters($this->getSubChapters($chapter));
            }
            $chapter->setOrder($data->order ?: $this->getOrder($chapter, $workFunctionId));
        }catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
        return $chapter;
    }
}