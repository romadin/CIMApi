<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-5-2019
 * Time: 15:52
 */

namespace App\Http\Controllers\WorkFunctions;


use App\Http\Handlers\ChaptersHandler;
use App\Http\Handlers\HeadlinesHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use Exception;
use Illuminate\Http\Request;

class WorkFunctionsController
{
    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;
    /**
     * @var HeadlinesHandler
     */
    private $headlinesHandler;
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;

    public function __construct(WorkFunctionsHandler $workFunctionsHandler, HeadlinesHandler $headlinesHandler, ChaptersHandler $chaptersHandler)
    {
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->headlinesHandler = $headlinesHandler;
        $this->chaptersHandler = $chaptersHandler;
    }

    public function getWorkFunctions(Request $request)
    {
        if( !$request->input('templateId') ) {
            return response('template id is not given', 400);
        }

        return $this->workFunctionsHandler->getWorkFunctions($request->input('templateId'));
    }

    public function getWorkFunction($id)
    {
        return $this->workFunctionsHandler->getWorkFunction($id);
    }

    public function postWorkFunction(Request $request)
    {
        if( !$request->input('templateId') ) {
            return response('template id is not given', 400);
        }
        if( !$request->input('name') ) {
            return response('Headline name is not given', 400);
        }

        return $this->workFunctionsHandler->postWorkFunction($request->post());
    }

    public function editWorkFunction(Request $request, $id)
    {
        $workFunction = $this->getWorkFunction($id);
        $postData = $request->post();
        if ($request->input('order')) {
            // change order
            return $this->workFunctionsHandler->reOrderWorkFunctions($workFunction, $request->input('order'));
        }
        if ($request->input('isMainFunction')) {
            $this->workFunctionsHandler->removeMainFunction($workFunction);
        }

        try {
            if (isset($postData['chapters'])) {
                $this->workFunctionsHandler->addChapters($workFunction, $postData['chapters']);
                unset($postData['chapters']);
            }
            if (isset($postData['headlines'])) {
                $this->workFunctionsHandler->addHeadlines($workFunction, $postData['headlines']);
                unset($postData['headlines']);
            }
        } catch (Exception $e) {
            return \response($e->getMessage(),500);
        }


        return $this->workFunctionsHandler->editWorkFunction($postData, $id);
    }

    public function deleteWorkFunction($id)
    {
        $workFunction = $this->getWorkFunction($id);

        if ($workFunction->isMainFunction()) {
            return response('Can not delete work function because this is the main function', 400);
        }

        $headlines = $this->headlinesHandler->getHeadlinesByWorkFunction($workFunction);
        if(!empty($headlines)) {
            foreach ($headlines as $headline) {
                $this->headlinesHandler->deleteHeadline($headline, $workFunction);
            }
        }

        $chapters = $this->chaptersHandler->getChaptersByParentWorkFunction($workFunction);
        if(!empty($chapters)) {
            foreach ($chapters as $chapter) {
                $this->chaptersHandler->deleteChapterAndLink($chapter, $workFunction);
            }
        }

        return $this->workFunctionsHandler->deleteWorkFunction($workFunction);
    }

}