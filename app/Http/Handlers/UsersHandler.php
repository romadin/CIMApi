<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 23-1-2019
 * Time: 20:31
 */

namespace App\Http\Handlers;


use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersHandler
{
    const USERS_TABLE = 'users';
    const ROLES_TABLE = 'roles';

    private $defaultSelect = [
        self::USERS_TABLE.'.id',
        self::USERS_TABLE.'.firstName',
        self::USERS_TABLE.'.insertion',
        self::USERS_TABLE.'.lastName',
        self::USERS_TABLE.'.email',
        self::USERS_TABLE.'.function',
        self::USERS_TABLE.'.password',
        self::USERS_TABLE.'.role_id',
        self::ROLES_TABLE.'.name as roleName'
    ];

    /**
     * @return User[]
     */
    public function getUsers()
    {
        try {
            $result = DB::table(self::USERS_TABLE)
                ->select($this->defaultSelect)
                ->join(self::ROLES_TABLE, self::USERS_TABLE.'.role_id', '=', self::ROLES_TABLE.'.id')
                ->get();
            if ( $result === null) {
                return response('Users are does not exist', 400);
            }
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        $users = [];

        foreach ($result as $user) {
            $userModel = $this->makeUser($user);
            $userModel->removePassword();
            array_push($users, $userModel);
        }

        return $users;
    }

    public function getUserByEmail(string $email): User
    {
        try {
            $result = DB::table(self::USERS_TABLE)
                ->select($this->defaultSelect)
                ->join(self::ROLES_TABLE, self::USERS_TABLE.'.role_id', '=', self::ROLES_TABLE.'.id')
                ->where(self::USERS_TABLE.'.email', '=', $email)
                ->first();
            if ( $result === null) {
                return response('Wrong credentials.', 502);
            }
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return $this->makeUser($result);
    }

    public function getUserById(int $id): User {
        try {
            $user = DB::table(self::USERS_TABLE)
                ->select($this->defaultSelect)
                ->join(self::ROLES_TABLE, self::USERS_TABLE.'.role_id', '=', self::ROLES_TABLE.'.id')
                ->where(self::USERS_TABLE.'.id', $id)
                ->first();
            if ( $user === null ) {
                return response('User doesnt exist', 400);
            }

        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return $this->makeUser($user);
    }

    private function makeUser($data): User {
        $role = new Role(
            $data->role_id,
            $data->roleName
        );

        $user = new User(
            $data->id,
            $data->firstName,
            $data->insertion ?: null,
            $data->lastName,
            $data->email,
            $data->function,
            $data->password,
            $role
        );

        return $user;
    }

}