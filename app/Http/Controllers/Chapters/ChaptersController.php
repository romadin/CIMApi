<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-5-2019
 * Time: 17:31
 */

namespace App\Http\Controllers\Chapters;

use App\Http\Handlers\ChaptersHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use App\Models\Chapter\Chapter;
use Exception;
use Illuminate\Http\Request;

class ChaptersController
{
    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;

    public function __construct(WorkFunctionsHandler $workFunctionsHandler, ChaptersHandler $chaptersHandler)
    {
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->chaptersHandler = $chaptersHandler;
    }

    public function getChapters(Request $request)
    {
        if( !$request->input('chapterId') && !$request->input('workFunctionId') ) {
            return response('parent id is not given', 400);
        }

        if( $request->input('chapterId') && $request->input('workFunctionId') ) {
            $chapter = $this->chaptersHandler->getChapter($request->input('chapterId'));
            return $this->chaptersHandler->getSubChapters($chapter);
        } else if ($request->input('workFunctionId')) {
            $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
            return $this->chaptersHandler->getChaptersByParentWorkFunction($workFunction);
        }

        return response('work function id is not given', 400);
    }

    public function getChapter(Request $request, $id)
    {
        return $this->chaptersHandler->getChapter($id, $request->input('workFunctionId'));
    }

    public function postChapter(Request $request)
    {
        if( !$request->input('parentChapterId') && !$request->input('workFunctionId') ) {
            return response('parent id is not given', 400);
        } else if( $request->input('parentChapterId') && $request->input('workFunctionId') ) {
            return response('too much parent id is given', 400);
        } else if( !$request->input('name') ) {
            return response('chapter name is not given', 400);
        }

        $postData = $request->post();
        try {
            if ($request->input('workFunctionId')) {
                $linkTable = WorkFunctionsHandler::MAIN_HAS_CHAPTER_TABLE;
                $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
                $newOrder = $this->workFunctionsHandler->getHighestOrder($linkTable, 'workFunctionId', $workFunction->getId()) + 1;
                $newChapter = $this->chaptersHandler->postChapter($postData, $request->input('workFunctionId'));

                try {
                    $this->workFunctionsHandler->createWorkFunctionHasChapters($workFunction, [$newChapter], [$newOrder]);
                } catch (Exception $e) {
                    return response($e->getMessage());
                }

                $newChapter->setOrder($newOrder);
            } else if(!$request->input('order')) {
                $order = $this->chaptersHandler->getHighestOrderInChapter($request->input('parentChapterId')) + 1;
                $postData['order'] = $order;
                $newChapter = $this->chaptersHandler->postChapter($postData, $request->input('workFunctionId'));
            } else {
                $newChapter = $this->chaptersHandler->postChapter($postData, $request->input('workFunctionId'));
            }
        } catch (Exception $e) {
            return response($e->getMessage());
        }

        return $newChapter;
    }

    public function editChapter(Request $request, int $id)
    {
        try {
            $chapter = $this->chaptersHandler->getChapter($id, $request->input('workFunctionId'));

            if ($request->input('workFunctionId')) {
                // reorder
                if ($request->input('order')) {
                    return $this->workFunctionsHandler->updateChildOrder($request->input('order'), $chapter, $request->input('workFunctionId'));
                }
            } else if ($request->input('order')) {
                $this->chaptersHandler->reOrderSubChapters($chapter, $request->input('order'));
            }

            foreach ($request->post() as $key => $data) {
                if ($data) {
                    $method = 'set'. ucfirst($key);
                    if(method_exists($chapter, $method)) {
                        $chapter->$method($data);
                    }
                }
            }

            return $this->chaptersHandler->updateChapter($chapter);
        } catch (Exception $e) {
            return response($e->getMessage());
        }

    }

    public function deleteChapter(Request $request, $id)
    {
        $chapter = $this->chaptersHandler->getChapter($id, $request->input('workFunctionId'));

        if(!$chapter instanceof Chapter) {
            return $chapter;
        }

        $workFunction = $request->input('workFunctionId') ? $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId')) : null;
        return $this->chaptersHandler->deleteChapterAndLink($chapter, $workFunction);
    }
}