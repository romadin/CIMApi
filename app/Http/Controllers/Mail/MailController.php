<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 21-3-2019
 * Time: 09:41
 */

namespace App\Http\Controllers\Mail;


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

    public function __construct(UsersHandler $usersHandler, OrganisationHandler $organisationHandler)
    {
        $this->userHandler = $usersHandler;
        $this->organisationHandler = $organisationHandler;
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
}