<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 11-5-2019
 * Time: 16:47
 */

namespace App\Http\Controllers\Headlines;


use App\Http\Handlers\ChaptersHandler;
use App\Http\Handlers\HeadlinesHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use App\Models\Headline\Headline;
use Illuminate\Http\Request;

class HeadlinesController
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

    public function getHeadlines(Request $request)
    {
        if( !$request->input('workFunctionId')) {
            return response('Work function id is not given', 400);
        }

        return $this->headlinesHandler->getHeadlinesByWorkFunction($this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId')));
    }

    public function getHeadline(Request $request, $id)
    {
        if( !$request->input('workFunctionId')) {
            return response('Work function id is not given', 400);
        }

        return $this->headlinesHandler->getHeadline($id, $request->input('workFunctionId'));
    }

    public function postHeadline(Request $request)
    {
        if( !$request->input('workFunctionId') ) {
            return response('Work function id is not given', 400);
        }
        if( !$request->input('name') ) {
            return response('Headline name is not given', 400);
        }
        $workFunctionId = $request->input('workFunctionId');

        $headline = $this->headlinesHandler->postHeadline($request->post(), $workFunctionId);

        if ($headline instanceof Headline) {
            $linkTable = WorkFunctionsHandler::MAIN_HAS_HEADLINE_TABLE;
            $newOrder = $this->workFunctionsHandler->getHighestOrderOfChildItems($workFunctionId, $linkTable, WorkFunctionsHandler::getLinkTableSibling($linkTable)) + 1;
            try {
                $this->workFunctionsHandler->createWorkFunctionHasHeadlines($this->workFunctionsHandler->getWorkFunction($workFunctionId), [$headline], [$newOrder]);
            } catch (\Exception $e) {
                return response($e->getMessage(), 500);
            }

            $headline->setOrder($newOrder);
        }

        return $headline;
    }

    public function editHeadline(Request $request, int $id)
    {
        $workFunctionId = $request->input('workFunctionId');
        if( !$workFunctionId ) {
            return response('Work function id is not given', 400);
        }

        if( $request->input('order') ) {
            return $this->workFunctionsHandler->updateChildOrder($request->input('order'), $this->headlinesHandler->getHeadline($id, $workFunctionId), $workFunctionId);
        }

        return $this->headlinesHandler->updateHeadline($request->post(), $id, $workFunctionId);
    }

    public function deleteHeadline(Request $request, int $id)
    {
        $workFunctionId = $request->input('workFunctionId');
        if( !$workFunctionId ) {
            return response('Work function id is not given', 400);
        }
        $headline = $this->getHeadline($request, $id);

        return $this->headlinesHandler->deleteHeadline($headline, $this->workFunctionsHandler->getWorkFunction($workFunctionId));
    }
}