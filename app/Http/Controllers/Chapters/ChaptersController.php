<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-5-2019
 * Time: 17:31
 */

namespace App\Http\Controllers\Chapters;


use App\Http\Handlers\ChaptersHandler;
use App\Http\Handlers\HeadlinesHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use App\Models\Chapter\Chapter;
use Illuminate\Http\Request;

class ChaptersController
{
    /**
     * @var HeadlinesHandler
     */
    private $headlinesHandler;
    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;

    public function __construct(HeadlinesHandler $headlinesHandler, WorkFunctionsHandler $workFunctionsHandler, ChaptersHandler $chaptersHandler)
    {
        $this->headlinesHandler = $headlinesHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->chaptersHandler = $chaptersHandler;
    }

    public function getChapters(Request $request)
    {
        if( !$request->input('headlineId') && !$request->input('workFunctionId') ) {
            return response('parent id is not given', 400);
        }

        if( $request->input('headlineId') && $request->input('workFunctionId') ) {
            $headline = $this->headlinesHandler->getHeadline($request->input('headlineId'), $request->input('workFunctionId'));
            return $this->chaptersHandler->getChaptersByParentHeadline($headline);
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
        if( !$request->input('headlineId') && !$request->input('workFunctionId') ) {
            return response('parent id is not given', 400);
        } else if( $request->input('headlineId') && $request->input('workFunctionId') ) {
            return response('too much parent id is given', 400);
        } else if( !$request->input('name') ) {
            return response('Headline name is not given', 400);
        }

        $postData = $request->post();
        if ($request->input('workFunctionId')) {
            $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
            $newOrder = $this->workFunctionsHandler->getHighestOrderOfChildItems($workFunction->getId()) + 1;
            $newChapter = $this->chaptersHandler->postChapter($postData, $request->input('workFunctionId'));
            $this->workFunctionsHandler->createWorkFunctionHasChapters($workFunction, [$newChapter], [$newOrder]);
            $newChapter->setOrder($newOrder);
        } else if(!$request->input('order')) {
            $order = $this->chaptersHandler->getHighestOrderInHeadline($request->input('headlineId')) + 1;
            $postData['order'] = $order;
            $newChapter = $this->chaptersHandler->postChapter($postData, $request->input('workFunctionId'));
        } else {
            $newChapter = $this->chaptersHandler->postChapter($postData, $request->input('workFunctionId'));
        }

        return $newChapter;
    }

    public function editChapter(Request $request, int $id)
    {
        $chapter = $this->chaptersHandler->getChapter($id, $request->input('workFunctionId'));

        if ($request->input('workFunctionId')) {
            // reorder
            if ($request->input('order')) {
                return $this->workFunctionsHandler->updateChildOrder($request->input('order'), $chapter, $request->input('workFunctionId'));
            }
        } else if ($request->input('order')) {
            $this->chaptersHandler->reOrderChaptersByHeadline($chapter, $request->input('order'));
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