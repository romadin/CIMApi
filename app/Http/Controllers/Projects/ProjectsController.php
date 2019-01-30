<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 8-1-2019
 * Time: 13:17
 */

namespace App\Http\Controllers\Projects;


use App\Http\Controllers\ApiController;
use App\Http\Controllers\Folders\FoldersController;
use App\Http\Handlers\FoldersHandler;
use App\Http\Handlers\UsersHandler;
use App\Models\Project\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectsController extends ApiController
{
    const PROJECT_TABLE = 'projects';
    const defaultFoldersTemplate = ['BIM-Team', 'BIM-Modelleur', 'BIM-Coördinator', 'BIM Regisseur', 'BIM Manager'];

    private $foldersHandler;
    private $usersHandlers;

    public function __construct(FoldersHandler $foldersHandler, UsersHandler $usersHandler)
    {
        $this->foldersHandler = $foldersHandler;
        $this->usersHandlers = $usersHandler;
    }

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
            return $this->updateProject($request, $id);
        }

        $newId = DB::table(self::PROJECT_TABLE)->insertGetId($request->post());

        if ( $newId ) {

            if ( $request->input('template')) {
                $template = $request->get('template') === 'default' ? self::defaultFoldersTemplate : $request->get('template');
                $this->foldersHandler->createFoldersTemplate($newId, $template);
            }
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
        $foldersDeleted = $this->foldersHandler->deleteFolderByProjectId($id);
        $linkedUsers = $this->usersHandlers->deleteProjectLink($id);

        if( $foldersDeleted && $linkedUsers ) {
            $deletedId = DB::table(self::PROJECT_TABLE)->delete($id);

            if( $deletedId ) {
                return $this->getProjects($request);
            }
            return response('Delete item went wrong', 400);
        }

        return response('Delete folders or linked users went wrong', 400);
    }

    /**
     * Update a existing project.
     * @param Request $request
     * @param $id
     * @return Project
     */
    private function updateProject(Request $request, $id)
    {
        DB::table(self::PROJECT_TABLE)
            ->where('id','=', $id)
            ->update($request->post());

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