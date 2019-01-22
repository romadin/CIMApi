<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 21-1-2019
 * Time: 15:38
 */

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Models\User;

class UsersController extends ApiController
{
    const TABLE_USER = 'users';
    const TABLE_USER_HAS_PROJECTS = 'users_has_projects';

    public function getUser(Request $request, $id) {

        $result = DB::table(self::TABLE_USER)
            ->where('id', '=', $id)
            ->first();
        if ( $result === null ) {
            return response('Item does not exist', 404);
        }

        $user = $this->makeUser($result);
        $user->removePassword();
        return $this->getReturnValueObject($request, $user);
    }

    public function postUser(Request $request) {
        $id = DB::table(self::TABLE_USER)->insertGetId([
            'firstName' => $request->input('firstName'),
            'insertion' => $request->input('insertion'),
            'lastName' => $request->input('lastName'),
            'email' => $request->input('email'),
            'function' => $request->input('function'),
            'password' => password_hash($request->input('password'), PASSWORD_DEFAULT),
        ]);

        if ( $id ) {
            foreach ($request->input('projects') as $projectId) {
                DB::table(self::TABLE_USER_HAS_PROJECTS)->insert([
                    'userId' => $id, 'projectId' => $projectId
                ]);
            }

            $user = DB::table(self::TABLE_USER)->where('id', $id)->first();

            return $this->getReturnValueObject($request, $this->makeUser($user));

        }

        return response('something went wrong', 400);

    }

    private function makeUser($data): User {
        $user = new User(
            $data->id,
            $data->firstName,
            $data->insertion ?: null,
            $data->lastName,
            $data->email,
            $data->password,
            $data->role_id
        );

        return $user;
    }

}