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
    const PROJECT_LINK_TABLE = 'users_has_projects';

    private $defaultSelect = [
        self::USERS_TABLE.'.id',
        self::USERS_TABLE.'.firstName',
        self::USERS_TABLE.'.insertion',
        self::USERS_TABLE.'.lastName',
        self::USERS_TABLE.'.email',
        self::USERS_TABLE.'.function',
        self::USERS_TABLE.'.password',
        self::USERS_TABLE.'.role_id',
        self::ROLES_TABLE.'.name as roleName',
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
                return response('Users does not exist', 400);
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

    public function getUsersByProjectId(int $projectId) {
        try {
            $result = DB::table(self::USERS_TABLE)
                ->select($this->defaultSelect)
                ->join(self::ROLES_TABLE, self::USERS_TABLE.'.role_id', '=', self::ROLES_TABLE.'.id')
                ->join(self::PROJECT_LINK_TABLE, self::USERS_TABLE. '.id', '=', self::PROJECT_LINK_TABLE. '.userId')
                ->where(self::PROJECT_LINK_TABLE.'.projectId', '=', $projectId)
                ->get();
            if ( $result === null) {
                return response('Users does not exist', 400);
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

    public function getUserByEmail(string $email)
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

    public function getUserById(int $id)
    {
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

    public function editUser($postData, $id)
    {
        try {
            DB::table(self::USERS_TABLE)
                ->where('id', $id)
                ->update($postData);
        } catch (\Exception $e) {
            return response('UsersHandler: There is something wrong with the database connection', 403);
        }

        return $this->getUserById($id);
    }

    /**
     * Delete the link between users and projects.
     * @param int $projectId
     * @return bool | \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function deleteProjectLink(int $projectId)
    {
        try {
            DB::table(self::PROJECT_LINK_TABLE)
                ->where('projectId', $projectId)
                ->delete();
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }
        return true;
    }

    private function getProjectsIdFromUser(User $user)
    {
        $projectsId = [];

        $result = DB::table(self::PROJECT_LINK_TABLE)
            ->select('projectId')
            ->where('userId', $user->getId())
            ->get();

        foreach ($result as $id) {
            array_push($projectsId, $id->projectId);
        }
        return $projectsId;
    }

    private function makeUser($data): User
    {
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

        $user->setProjectsId($this->getProjectsIdFromUser($user));

        return $user;
    }

}