<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 8-1-2019
 * Time: 13:17
 */

namespace App\Http\Controllers\Projects;


use App\Http\Controllers\ApiController;
use App\Http\Controllers\Templates\TemplatesController;
use App\Http\Handlers\ActionsHandler;
use App\Http\Handlers\DocumentsHandler;
use App\Http\Handlers\EventsHandler;
use App\Http\Handlers\OrganisationHandler;
use App\Http\Handlers\ProjectsHandler;
use App\Http\Handlers\UsersHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use App\Models\Project\Project;
use App\Models\Template\Template;
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProjectsController extends ApiController
{
    /**
     * @var ProjectsHandler
     */
    private $projectsHandler;

    /**
     * @var UsersHandler
     */
    private $usersHandler;

    /**
     * @var ActionsHandler
     */
    private $actionHandler;

    /**
     * @var EventsHandler
     */
    private $eventsHandler;

    /**
     * @var TemplatesController
     */
    private $templateController;

    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;

    /**
     * @var WorkFunctionsHandler
     */
    private $organisationHandler;

    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;
    /**
     * ProjectsController constructor.
     * @param ProjectsHandler $projectsHandler
     * @param UsersHandler $usersHandlers
     * @param ActionsHandler $actionHandlers
     * @param EventsHandler $eventsHandler
     * @param TemplatesController $templatesController
     * @param WorkFunctionsHandler $workFunctionsHandler
     * @param OrganisationHandler $organisationHandler
     * @param DocumentsHandler $documentsHandler
     */
    public function __construct(
        ProjectsHandler $projectsHandler,
        UsersHandler $usersHandlers,
        ActionsHandler $actionHandlers,
        EventsHandler $eventsHandler,
        TemplatesController $templatesController,
        WorkFunctionsHandler $workFunctionsHandler,
        OrganisationHandler $organisationHandler,
        DocumentsHandler $documentsHandler)
    {
        $this->projectsHandler = $projectsHandler;
        $this->usersHandler = $usersHandlers;
        $this->actionHandler = $actionHandlers;
        $this->eventsHandler = $eventsHandler;
        $this->templateController = $templatesController;
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->organisationHandler = $organisationHandler;
        $this->documentsHandler = $documentsHandler;
    }


    /**
     * Get all of the projects.
     * @param Request $request
     * @return array
     */
    public function getProjects(Request $request)
    {
        if(!$request->input('organisationId')) {
            return response('organisation id is not given', 404);
        }

        return $this->getReturnValueArray($request, $this->projectsHandler->getProjectsByOrganisation($request->input('organisationId')));
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
        try {
            if ( $id ) {
                return $this->updateProject($request, $id);
            }
            if ( !$request->input('templateId')) {
                return response('No template was given', 400);
            }

            $postData = [
                'name' => $request->input('name'),
                'organisationId' => $request->input('organisationId'),
            ];

            /**
             * Security check for demo organisation maximum of 1 project
             */
            $passCheck = $this->checkDemo($request->input('organisationId'));
            if ($passCheck instanceof Response) { return $passCheck; }

            $project = $this->projectsHandler->postProject($postData);

            if ( $project ) {

                /** @var Template|Response $template */
                $template = $this->templateController->getTemplate($request->input('templateId'));
                if ($template instanceof Response) {
                    return $template;
                }

                $workFunctions = $template->getWorkFunctions();
                usort($workFunctions, function ($a, $b) {
                    return $a->getOrder() - $b->getOrder();
                });

                foreach ($workFunctions as $workFunction) {
                    $postData = [
                        'name' => $workFunction->getName(),
                        'isMainFunction' => $workFunction->isMainFunction(),
                        'projectId' => $project->getId(),
                        'fromTemplate' => true
                    ];

                    $workFunction = $this->workFunctionsHandler->postWorkFunction($postData, 'projectId');

                    if($workFunction->isMainFunction()) {
                        $mainWorkFunctionFromTemplate = array_filter($workFunctions, function(WorkFunction $workFunction) { return $workFunction->isMainFunction(); });
                        /** @var WorkFunction $mainWorkFunction */
                        $mainWorkFunction = reset($mainWorkFunctionFromTemplate);
                        if(isset($mainWorkFunction)) {
                            $this->documentsHandler->createDocumentsWithTemplate($workFunction, $mainWorkFunction->getChapters(), WorkFunctionsHandler::MAIN_HAS_DOCUMENT_TABLE);
                        }
                    }
                }

                return $this->getReturnValueObject($request, $project);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return response('something went wrong', 400);
    }

    /**
     * Delete an project with the given id.
     * @param Request $request
     * @param $id
     * @return array|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function deleteProject(Request $request, int $id)
    {
        try {
            $workFunctions = $this->workFunctionsHandler->getWorkFunctionsFromProjectId($id);
            foreach ($workFunctions as $workFunction) {
                $this->workFunctionsHandler->deleteWorkFunction($workFunction);
            }
            $linkedUsers = $this->usersHandler->deleteProjectLink($id);
            $linkedActionsDeleted = $this->actionHandler->deleteActionByProjectId($id);
            $linkedEvents = $this->eventsHandler->deleteEventByProjectId($id);

            if( $linkedUsers && $linkedActionsDeleted && $linkedEvents) {

                DB::table(ProjectsHandler::PROJECT_TABLE)->delete($id);
                return json_encode('Project deleted');
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 200);
        }


        return response('Deleting the linked items went wrong', 400);
    }

    /**
     * Update a existing project.
     * @param Request $request
     * @param $id
     * @return Project
     */
    private function updateProject(Request $request, $id)
    {
        DB::table(ProjectsHandler::PROJECT_TABLE)
            ->where('id','=', $id)
            ->update($request->post());

        $result = DB::table(ProjectsHandler::PROJECT_TABLE)->where('id', $id)->first();

        return $this->projectsHandler->makeProject($result);
    }

    /**
     * @param int $organisationId
     * @return bool|Response|\Laravel\Lumen\Http\ResponseFactory
     */
    private function checkDemo(int $organisationId) {
        try {
            $organisation = $this->organisationHandler->getOrganisationById($organisationId);

            if ($organisation->getDemo() && $this->projectsHandler->getProjectsAmountByOrganisation($organisation->getId()) === 1) {
                return response('Maximum amount of projects has been reached.', 400);
            }
        } catch  (Exception $e) {
            return response($e->getMessage(), 200);
        }
        return true;
    }

}
