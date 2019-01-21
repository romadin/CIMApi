<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 8-1-2019
 * Time: 13:17
 */

namespace App\Http\Controllers\Projects;


use App\Http\Controllers\ApiController;
use App\Models\Project\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectsController extends ApiController
{
    const PROJECT_TABLE = 'projects';

    /**
     * Get all of the projects.
     * @param Request $request
     * @return array
     */
    public function getProjects(Request $request)
    {
        $results = DB::table(self::PROJECT_TABLE)->get();

        $projects = [];

        foreach ($results as $result) {
            array_push($projects, $this->makeProject($result));
        }

        return $this->getReturnValueArray($request, $projects);
    }

    /**
     * Get an single project.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response|\JsonSerializable|\Laravel\Lumen\Http\ResponseFactory|object|string
     */
    public function getProject(Request $request, $id)
    {
        $result = DB::table(self::PROJECT_TABLE)
            ->where('id', '=', $id)
            ->first();
        if ( $result === null ) {
            return response('Item does not exist', 404);
        }

        return $this->getReturnValueObject($request, $this->makeProject($result));
    }

    /**
     * Create a new project. If id is given update that project.
     * @param Request $request
     * @param null $id
     * @return Project|\Illuminate\Http\Response|\JsonSerializable|\Laravel\Lumen\Http\ResponseFactory|object|string
     */
    public function createOrUpdateProject(Request $request, $id = null)
    {
        if ( $id ) {
            return $this->updateProject($id);
        }

        $newId = DB::table(self::PROJECT_TABLE)->insertGetId([$_POST]);

        if ( $newId ) {
            $result = DB::table(self::PROJECT_TABLE)->where('id', $newId)->first();

            return $this->getReturnValueObject($request, $this->makeProject($result));
        }

        return response('something went wrong', 400);

    }

    /**
     * Delete an project with the given id.
     * @param Request $request
     * @param $id
     * @return array|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function deleteProject(Request $request, $id)
    {
        $deletedId = DB::table(self::PROJECT_TABLE)->delete($id);

        if( $deletedId ) {
            return $this->getProjects($request);
        }

        return response('Delete item went wrong', 400);
    }

    /**
     * Update a existing project.
     * @param $id
     * @return Project
     */
    private function updateProject($id)
    {
        DB::table(self::PROJECT_TABLE)
            ->where('id','=', $id)
            ->update($_POST);

        $result = DB::table(self::PROJECT_TABLE)->where('id', $id)->first();

        return $this->makeProject($result);
    }

    /**
     * Make an project model from the given data.
     * @param $data
     * @return Project
     */
    private function makeProject($data)
    {
        $project = new Project(
            $data->id,
            $data->name
        );
        $project->setActionListId($data->action_list_id);
        $project->setAgendaId($data->agenda_id);

        return $project;
    }
}