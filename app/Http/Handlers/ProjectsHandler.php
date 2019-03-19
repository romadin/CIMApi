<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 17-2-2019
 * Time: 10:24
 */

namespace App\Http\Handlers;


use App\Models\Project\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProjectsHandler
{
    const PROJECT_TABLE = 'projects';

    /**
     * @param int $id
     * @return Project | Response
     */
    public function getProject(int $id)
    {
        try {
            $result = DB::table(self::PROJECT_TABLE)
                ->where('id', '=', $id)
                ->first();
            if ( $result === null ) {
                return response('Item does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('ProjectHandler: There is something wrong with the database connection',500);
        }

        return $this->makeProject($result);

    }

    /**
     * Make an project model from the given data.
     * @param $data
     * @return Project
     */
    public function makeProject($data)
    {
        $project = new Project(
            $data->id,
            $data->name,
            $data->organisationId
        );
        $project->setActionListId($data->action_list_id);
        $project->setAgendaId($data->agenda_id);

        return $project;
    }

}