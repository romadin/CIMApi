<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-5-2019
 * Time: 15:52
 */

namespace App\Http\Controllers\WorkFunctions;


use App\Http\Handlers\ChaptersHandler;
use App\Http\Handlers\CompaniesHandler;
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
        if($request->input('templateId')) {
            $parentIdName = 'templateId';
        }elseif ($request->input('projectId')) {
            $parentIdName = 'projectId';
        } else {
            return response('parent id is not given', 400);
        }
        $parentId = $request->input($parentIdName);

        return $this->workFunctionsHandler->getWorkFunctionsFromTemplateId($parentId, $parentIdName);
    }

    public function getWorkFunction($id)
    {
        return $this->workFunctionsHandler->getWorkFunction($id);
    }

    public function postWorkFunction(Request $request)
    {
        if( !$request->input('templateId') && !$request->input('projectId')  ) {
            return response('parent id is not given', 400);
        }
        if( !$request->input('name') ) {
            return response('work function name is not given', 400);
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
            // add connections
            if (isset($postData['chapters'])) {
                $this->workFunctionsHandler->addChildItems($workFunction, $postData['chapters'], 'chapterId', WorkFunctionsHandler::MAIN_HAS_CHAPTER_TABLE);
                unset($postData['chapters']);
            }
            if (isset($postData['headlines'])) {
                $this->workFunctionsHandler->addChildItems($workFunction, $postData['headlines'], 'headlineId', WorkFunctionsHandler::MAIN_HAS_HEADLINE_TABLE);
                unset($postData['headlines']);
            }
            if (isset($postData['documents'])) {
                $this->workFunctionsHandler->addChildItems($workFunction, $postData['documents'], 'documentId', WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE);
                unset($postData['documents']);
            }
            if (isset($postData['folders'])) {
                $this->workFunctionsHandler->addChildItems($workFunction, $postData['folders'], 'folderId', WorkFunctionsHandler::MAIN_HAS_FOLDER_TABLE);
                unset($postData['folders']);
            }
            if (isset($postData['companies'])) {
                $this->workFunctionsHandler->addChildItems($workFunction, $postData['companies'], 'companyId', CompaniesHandler::TABLE_LINK_WORK_FUNCTION);
                unset($postData['companies']);
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

        return $this->workFunctionsHandler->deleteWorkFunction($workFunction);
    }

}