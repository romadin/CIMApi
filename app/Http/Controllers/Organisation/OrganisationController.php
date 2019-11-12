<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-3-2019
 * Time: 11:42
 */

namespace App\Http\Controllers\Organisation;


use App\Http\Controllers\ApiController;
use App\Http\Controllers\Mail\MailController;
use App\Http\Handlers\OrganisationHandler;
use App\Http\Handlers\UsersHandler;
use Illuminate\Http\Request;

class OrganisationController extends ApiController
{
    /**
     * @var OrganisationHandler
     */
    private $organisationHandler;
    /**
     * @var UsersHandler
     */
    private $userHandler;
    /**
     * @var MailController
     */
    private $mailController;

    public function __construct(OrganisationHandler $organisationHandler, UsersHandler $userHandler, MailController $mailController)
    {
        $this->organisationHandler = $organisationHandler;
        $this->userHandler = $userHandler;
        $this->mailController = $mailController;
    }

    public function getOrganisation(Request $request)
    {
        return $this->getReturnValueObject($request, $this->organisationHandler->getOrganisationByName($request->input('name')));
    }

    public function createOrganisation(Request $request)
    {
        try {
            $organisation = $this->organisationHandler->createOrganisation($request->input('name'));

            if ($organisation === true) {
                $organisation = $this->organisationHandler->getOrganisationByName($request->input('name'));
            } else {
                $userResponse = $request->input('user');
                $user = $this->userHandler->postUser($userResponse, false, $organisation->getId());
                $this->mailController->sendUserActivation($user->getId());
            }
        } catch (\Exception $e) {
            return response($e->getMessage());
        }

        return $this->getReturnValueObject($request, $organisation);
    }

    public function addUsersToOrganisation(Request $request, int $id)
    {
        if(!$request->input('maxUsers')) {
            return response('Only users can be updated', 400);
        }

        return $this->getReturnValueObject($request, $this->organisationHandler->updateOrganisation(['maxUsers' => $request->input('maxUsers')], $id, null));
    }

    public function getOrganisationImage(int $id)
    {
        return $this->organisationHandler->getImage($id);
    }

    public function updateOrganisation(Request $request, int $id)
    {
        if ($request->input('name')) {
            return response('Update name not permitted', 200);
        }

        return $this->getReturnValueObject($request, $this->organisationHandler->updateOrganisation($request->post(), $id, $request->file('logo')));
    }

}
