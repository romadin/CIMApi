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
    const SELECT_ACTION_TABLE = [
        self::ACTION_TABLE.'.id',
        self::ACTION_TABLE.'.code',
        self::ACTION_TABLE.'.description',
        self::ACTION_TABLE.'.holder',
        self::ACTION_TABLE.'.week',
        self::ACTION_TABLE.'.status',
        self::ACTION_TABLE.'.comments',
        self::ACTION_TABLE.'.isDone',
    ];

    public function getActionsForProject($projectId)
    {
        try {
            $result = DB::table(self::ACTION_TABLE)
                ->select(self::SELECT_ACTION_TABLE)
                ->join(self::ACTION_LINK_PROJECTS, self::ACTION_TABLE . '.id', '=', self::ACTION_LINK_PROJECTS . '.actionId')
                ->where(self::ACTION_LINK_PROJECTS . '.projectId', $projectId)
                ->get();
            if($result === null) {
                return response('There are no actions at for the project', 400);
            }
        } catch (\Exception $e) {
            return response('ActionsHandler: There is something wrong with the database connection', 403);
        }
        $actions = [];

        foreach ($result as $item) {
            array_push($actions, $this->makeAction($item));
        }

        return $actions;
    }
    
    private function makeAction($data): Action
    {
        $action = new Action(
            $data->id,
            $data->code,
            $data->description,
            $data->holder,
            $data->week,
            $data->status,
            $data->comments,
            $data->isDone
        );

        return $action;
    }

}