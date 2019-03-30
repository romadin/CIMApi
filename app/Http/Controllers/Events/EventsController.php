<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 30-3-2019
 * Time: 08:45
 */

namespace App\Http\Controllers\Events;

use App\Http\Controllers\ApiController;
use App\Http\Handlers\EventsHandler;
use Illuminate\Http\Request;

class EventsController extends ApiController
{
    private $eventsHandler;

    public function __construct(EventsHandler $eventsHandler)
    {
        $this->eventsHandler = $eventsHandler;
    }

    public function getEvents(Request $request)
    {
        $projectId = $request->input('projectId');

        if (empty($projectId)) {
            return response('Project id is missing', 200);
        }

        return $this->getReturnValueArray($request, $this->eventsHandler->getEventsByProjectId($projectId));
    }

}