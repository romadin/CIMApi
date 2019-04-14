<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 30-3-2019
 * Time: 08:47
 */

namespace App\Http\Handlers;


use App\Models\Event\Event;
use App\Models\Event\Location;
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

    public function getEventById(int $id)
    {
        try {
            $result = DB::table(self::EVENTS_TABLE)
                ->where('id', $id)
                ->first();
            if ($result === null) {
                return response('event not found', 200);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $this->makeEvent($result);
    }

    public function editEvent($id, $postData)
    {
        try {
            DB::table(self::EVENTS_TABLE)
                ->where('id', $id)
                ->update($postData);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $this->getEventById($id);
    }

    public function postEvent($postData)
    {
        try {
            $id = DB::table(self::EVENTS_TABLE)
                ->insertGetId($postData);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $this->getEventById($id);
    }

    public function deleteEvent(int $id)
    {
        try {
            DB::table(self::EVENTS_TABLE)
                ->where('id', $id)
                ->delete();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return json_encode('Event has been deleted');
    }

    public function deleteEventByProjectId(int $projectId)
    {
        try {
            DB::table(self::EVENTS_TABLE)
                ->where('projectId', $projectId)
                ->delete();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return response('Events has been deleted', 200);
    }

    private function makeEvent($data): Event
    {
        $event = new Event();
        foreach ($data as $key => $value) {
            if ($value) {
                if ($key === 'location') {
                    $event->setLocation($this->makeLocation(json_decode($value)));
                    continue;
                }
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

    private function makeLocation($locationData): Location
    {
        $location = new Location();
        $location->setStreetName($locationData->streetName);
        $location->setResidence($locationData->residence);

        return $location;
    }

}