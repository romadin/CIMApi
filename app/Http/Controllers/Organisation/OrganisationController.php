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
use App\Mail\ReminderDemoVersion;
use App\Mail\UserActivation;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
            $demoPeriod = $request->input('demo') ? new DateTime() : null;
            $organisation = $this->organisationHandler->createOrganisation($request->input('name'), $demoPeriod);

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

    public function updateOrganisationStrict(Request $request, int $id)
    {
        if (!$request->input('maxUsers') && !$request->input('demoPeriod') ) {
            return response('Action is not permitted', 400);
        }

        $updateData = [];
        if($request->input('maxUsers')) {
            $updateData['maxUsers'] = $request->input('maxUsers');
        }
        if ($request->input('demoPeriod')) {
            $updateData['demoPeriod'] = null;
        }

        return $this->getReturnValueObject($request, $this->organisationHandler->updateOrganisation($updateData, $id, null));
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

    public function getTest()
    {

    }

}
