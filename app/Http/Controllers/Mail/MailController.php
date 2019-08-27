<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 21-3-2019
 * Time: 09:41
 */

namespace App\Http\Controllers\Mail;


use App\Http\Handlers\ProjectsHandler;
use App\Mail\UserAddedToProject;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Handlers\OrganisationHandler;
use App\Http\Controllers\ApiController;
use App\Http\Handlers\UsersHandler;
use App\Mail\UserActivation;

class MailController extends ApiController
{
    /**
     * @var UsersHandler
     */
    private $userHandler;

    /**
     * @var OrganisationHandler
     */
    private $organisationHandler;

    /**
     * @var ProjectsHandler
     */
    private $projectsHandler;

    public function __construct(UsersHandler $usersHandler, OrganisationHandler $organisationHandler, ProjectsHandler $projectsHandler)
    {
        $this->userHandler = $usersHandler;
        $this->organisationHandler = $organisationHandler;
        $this->projectsHandler = $projectsHandler;
    }

    public function sendUserActivation($id)
    {
        $user = $this->userHandler->getUserById($id);
        $organisation = $this->organisationHandler->getOrganisationById($user->getOrganisationId());

        // sendMail
        Mail::to($user->getEmail())
            ->send(new UserActivation($user, $organisation));

        return json_encode('success');
    }

    public function sendUserAddedToProject($id, $projectId)
    {
        $user = $this->userHandler->getUserById($id);
        $project = $this->projectsHandler->getProject($projectId);
        $organisation = $this->organisationHandler->getOrganisationById($user->getOrganisationId());

        // sendMail
        Mail::to($user->getEmail())
            ->send(new UserAddedToProject($user, $organisation, $project));

        return json_encode('success');
    }
}