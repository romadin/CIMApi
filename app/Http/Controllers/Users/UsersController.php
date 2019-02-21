<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 21-1-2019
 * Time: 15:38
 */

namespace App\Http\Controllers\Users;

use App\Http\Handlers\UsersHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Auth\Factory as Auth;

use App\Mail\UserActivation;
use App\Http\Controllers\ApiController;

class UsersController extends ApiController
{
    /**
     * The authentication guard factory instance.
     *
     * @var Auth
     */
    protected $auth;

    /**
     * @var UsersHandler
     */
    private $usersHandler;

    public function __construct(UsersHandler $usersHandler, Auth $auth)
    {
        $this->usersHandler = $usersHandler;
        $this->auth = $auth;
    }

    public function getUsers(Request $request)
    {
        if($request->input('projectId')) {
            return $this->getReturnValueArray($request, $this->usersHandler->getUsersByProjectId((int)$request->input('projectId')));
        }
        return $this->getReturnValueArray($request, $this->usersHandler->getUsers());
    }

    public function getUser(Request $request, $id)
    {
        $user = $this->usersHandler->getUserById($id);
        return $this->getReturnValueObject($request, $user);
    }

    public function getUserActivation(Request $request)
    {
        $user = $this->auth->guard(null)->user();
        return $this->getReturnValueObject($request, $user);
    }

    public function postUser(Request $request, $id = null)
    {
        if ($id) {
            return $this->getReturnValueObject($request,
                $this->usersHandler->editUser($request->post(), $id, $request->file('image'), $request->input('activationToken') ));
        }

        $user =  $this->usersHandler->postUser($request->post(), $request->file('image'));
        // sendMail
        Mail::to($request->input('email'))
            ->send(new UserActivation($user));

        return $this->getReturnValueObject($request, $user);
    }

    public function getUserImage(Request $request, $id)
    {
        return $this->usersHandler->getImage($id)->image;
    }
}