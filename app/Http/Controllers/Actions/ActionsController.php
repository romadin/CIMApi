<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 7-2-2019
 * Time: 23:36
 */

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\ApiController;
use App\Http\Handlers\ActionsHandler;
use Illuminate\Http\Request;

class ActionsController extends ApiController
{
    /**
     * @var ActionsHandler
     */
    private $actionHandler;

    /**
     * ActionsController constructor.
     * @param ActionsHandler $actionHandler
     */
    public function __construct(ActionsHandler $actionHandler)
    {
        $this->actionHandler = $actionHandler;
    }

    public function getActions(Request $request)
    {
        if(!$request->input('projectId')) {
            return response('Project id is missing', 400);
        }
        return $this->getReturnValueArray($request, $this->actionHandler->getActionsForProject($request->input('projectId')));
    }

    public function createOrUpdateAction(Request $request, $id = null)
    {
        if ($id) {
            return $this->getReturnValueObject($request, $this->actionHandler->updateAction($request->post(), $id));
        }

        if(!$request->input('projectId')) {
            return response('Project id is missing', 400);
        }

        if(!$request->input('description')) {
            return response('Description content is missing', 400);
        }

        return $this->getReturnValueObject($request, $this->actionHandler->createAction($request->post()));
    }

    public function deleteAction(Request $request, $id = null)
    {
        if ($id) {
            if ($this->actionHandler->deleteAction($id)) {
                return response('Deleted action: ' . $id, 200);
            }
        }

        if($request->input('projectId')) {
            if ($this->actionHandler->deleteActionByProjectId($request->input('projectId'))) {
                return response('Deleted all the actions for project: ' . $request->input('projectId') , 200);
            }

        }

        return response('Deleting the action did not work, try again later', 400);
    }

}