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
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Models\User;

class UsersController extends ApiController
{
    const TABLE_USER = 'users';
    const TABLE_USER_HAS_PROJECTS = 'users_has_projects';

    /**
     * @var UsersHandler
     */
    private $usersHandler;

    public function __construct(UsersHandler $usersHandler)
    {
        $this->usersHandler = $usersHandler;
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
        $user->removePassword();
        return $this->getReturnValueObject($request, $user);
    }

    public function postUser(Request $request, $id = null)
    {
        if ($id) {
            return $this->getReturnValueObject($request, $this->usersHandler->editUser($request->post(), $id));
        }

        $newId = DB::table(self::TABLE_USER)->insertGetId([
            'firstName' => $request->input('firstName'),
            'insertion' => $request->input('insertion'),
            'lastName' => $request->input('lastName'),
            'email' => $request->input('email'),
            'function' => $request->input('function'),
            'password' => password_hash($request->input('password'), PASSWORD_DEFAULT),
        ]);

        if ( $newId ) {
            // insert the link for the user to the projects.
            foreach ($request->input('projectsId') as $projectId) {
                DB::table(self::TABLE_USER_HAS_PROJECTS)->insert([
                    'userId' => $newId, 'projectId' => $projectId
                ]);
            }

            return $this->getReturnValueObject($request, $this->usersHandler->getUserById($newId));
        }

        return response('something went wrong', 400);
    }
}