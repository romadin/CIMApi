<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 7-2-2019
 * Time: 23:48
 */

namespace App\Http\Handlers;


use App\Models\Action\Action;
use Illuminate\Support\Facades\DB;

class ActionsHandler
{
    const ACTION_TABLE = 'actions';
    const ACTION_LINK_PROJECTS = 'projects_has_actions';

    public function getActionsForProject($projectId)
    {
        $actions = [];
        try {
            $result = DB::table(self::ACTION_TABLE)
                ->where('projectId', $projectId)
                ->get();
        } catch (\Exception $e) {
            return response('ActionsHandler: There is something wrong with the database connection', 403);
        }

        foreach ($result as $item) {
            array_push($actions, $this->makeAction($item));
        }

        return $actions;
    }

    public function getActionById(int $id)
    {
        try {
            $result = DB::table(self::ACTION_TABLE)
                ->where('id', $id)
                ->first();
        } catch (\Exception $e) {
            return response('ActionsHandler: There is something wrong with the database connection', 403);
        }
        return $this->makeAction($result);
    }

    public function createAction($postData)
    {
        $postData['code'] = $this->getLatestCodeFromProject($postData['projectId']) + 1;

        try {
            $newActionId = DB::table(self::ACTION_TABLE)->insertGetId($postData);
        } catch (\Exception $e) {
            return response('ActionsHandler: There is something wrong with the database connection', 403);
        }
        return $this->getActionById($newActionId);
    }

    public function updateAction($updateData, int $id)
    {
        try {
            DB::table(self::ACTION_TABLE)
                ->where('id', $id)
                ->update($updateData);
        } catch (\Exception $e) {
            return response('ActionsHandler: There is something wrong with the database connection', 403);
        }
        return $this->getActionById((int)$id);
    }

    public function deleteAction(int $id)
    {
        try {
            DB::table(self::ACTION_TABLE)
                ->where('id', $id)
                ->delete();
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return true;
    }

    public function deleteActionByProjectId(int $projectId)
    {
        try {
            DB::table(self::ACTION_TABLE)
                ->where('projectId', $projectId)
                ->delete();
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return true;
    }

    private function getLatestCodeFromProject(int $projectId): int
    {
        try {
            $result = DB::table(self::ACTION_TABLE)
                ->select('code')
                ->where('projectId', $projectId)
                ->orderByDesc('code')
                ->first();
            if ($result == null) {
                return 0;
            }
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return $result->code;
    }
    
    private function makeAction($data): Action
    {
        $action = new Action(
            $data->id,
            $data->code,
            $data->description,
            $data->actionHolder,
            $data->week,
            $data->comments,
            $data->isDone,
            $data->projectId
        );

        return $action;
    }

}