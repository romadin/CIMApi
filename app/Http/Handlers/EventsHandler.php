<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 30-3-2019
 * Time: 08:47
 */

namespace App\Http\Handlers;


use App\Models\Event\Event;
use DateTime;
use Illuminate\Support\Facades\DB;

class EventsHandler
{
    const EVENTS_TABLE = 'events';

    public function getEventsByProjectId(int $projectId)
    {
        $events = [];
        try {
            $result = DB::table(self::EVENTS_TABLE)
                ->where('projectId', $projectId)
                ->get();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        foreach ($result as $eventResult) {
            array_push($events, $this->makeEvent($eventResult));
        }

        return $events;
    }

    private function makeEvent($data): Event
    {
        $event = new Event();
        foreach ($data as $key => $value) {
            if ($value) {
                $method = 'set'. ucfirst($key);
                if(method_exists($event, $method)) {
                    try {
                        $value = new DateTime($value);
                    } catch (\Exception $e) {}
                    // we ignore the error
                    $event->$method($value);
                }
            }
        }
        return $event;
    }

}