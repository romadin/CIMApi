<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 21-1-2019
 * Time: 15:38
 */

namespace App\Http\Controllers\Users;

use App\Http\Handlers\UsersHandler;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Factory as Auth;

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

    public function createUser(Request $request)
    {
        $this->setValidators($request->post());
        $user = $this->usersHandler->postUser($request->post(), $request->file('image'), $request->input('organisationId'));

        return $this->getReturnValueObject($request, $user);
    }

    public function editUser(Request $request, $id)
    {
        $user = $this->usersHandler->editUser($request->post(), $id, $request->file('image'), $request->input('activationToken'));
        return $this->getReturnValueObject($request, $user);
    }

    public function getUserImage(Request $request, $id)
    {
        return $this->usersHandler->getImage($id)->image;
    }

    public function deleteUser(Request $request, $id) {
        if ($request->input('projectId')) {
            /** Delete the link between user and project. Keep the user if he has more project links*/
            return $this->usersHandler->deleteUserByProjectLink($id, $request->input('projectId'));
        }

        return $this->usersHandler->deleteUser($id);
    }

    private function setValidators($requestData)
    {
        $messages = [
            'email.unique' => 'Duplicate email',
            'phoneNumber.unique' => 'Duplicate phone number'
        ];
        $rules =  [
            'email' => 'unique:users',
            'phoneNumber' => 'unique:users',
        ];
        $validator = Validator::make($requestData, $rules, $messages);

        if($validator->fails()) {
            return json_encode($validator->errors()->messages());
        }
    }
}