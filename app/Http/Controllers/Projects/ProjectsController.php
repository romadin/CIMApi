<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 8-1-2019
 * Time: 13:17
 */

namespace App\Http\Controllers\Projects;


use App\Http\Controllers\ApiController;
use App\Http\Handlers\ActionsHandler;
use App\Http\Handlers\FoldersHandler;
use App\Http\Handlers\ProjectsHandler;
use App\Http\Handlers\UsersHandler;
use App\Models\Project\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectsController extends ApiController
{
    const PROJECT_TABLE = 'projects';
    const defaultFoldersTemplate = [
        ['name' => 'BIM-Uitvoeringsplan', 'order' => 0, 'fromTemplate' => true],
        ['name' => 'BIM-Modelleur', 'order' => 0, 'fromTemplate' => true],
        ['name' => 'BIM-Coördinator', 'order' => 0, 'fromTemplate' => true],
        ['name' => 'BIM Regisseur', 'order' => 0, 'fromTemplate' => true],
        ['name' => 'BIM Manager', 'order' => 0, 'fromTemplate' => true],
    ];

    /**
     * @var ProjectsHandler
     */
    private $projectsHandler;

    /**
     * @var FoldersHandler
     */
    private $foldersHandler;

    /**
     * @var UsersHandler
     */
    private $usersHandlers;

    /**
     * @var ActionsHandler
     */
    private $actionHandlers;

    /**
     * ProjectsController constructor.
     * @param ProjectsHandler $projectsHandler
     * @param FoldersHandler $foldersHandler
     * @param UsersHandler $usersHandlers
     * @param ActionsHandler $actionHandlers
     */
    public function __construct(ProjectsHandler $projectsHandler, FoldersHandler $foldersHandler, UsersHandler $usersHandlers, ActionsHandler $actionHandlers)
    {
        $this->projectsHandler = $projectsHandler;
        $this->foldersHandler = $foldersHandler;
        $this->usersHandlers = $usersHandlers;
        $this->actionHandlers = $actionHandlers;
    }


    /**
     * Get all of the projects.
     * @param Request $request
     * @return array
     */
    public function getProjects(Request $request)
    {
        $results = DB::table(self::PROJECT_TABLE)->where('organisationId', $request->input('organisationId'))->get();

        $projects = [];

        foreach ($results as $result) {
            array_push($projects, $this->projectsHandler->makeProject($result));
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
        return $this->getReturnValueObject($request, $this->projectsHandler->getProject($id));
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
                $this->foldersHandler->createFoldersTemplate($template, $newId);
            }
            $result = DB::table(self::PROJECT_TABLE)->where('id', $newId)->first();

            return $this->getReturnValueObject($request, $this->projectsHandler->makeProject($result));
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
        $foldersDeleted = $this->foldersHandler->deleteFolders($this->foldersHandler->getFoldersByProjectId($id));
        $linkedUsers = $this->usersHandlers->deleteProjectLink($id);
        $linkedActionsDeleted = $this->actionHandlers->deleteActionByProjectId($id);

        if( $foldersDeleted && $linkedUsers && $linkedActionsDeleted ) {
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

        return $this->projectsHandler->makeProject($result);
    }

}