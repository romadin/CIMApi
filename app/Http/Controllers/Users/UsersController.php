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