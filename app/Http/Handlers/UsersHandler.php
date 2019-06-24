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
use Exception;
use Illuminate\Support\Facades\DB;

class UsersHandler
{
    const USERS_TABLE = 'users';
    const ROLES_TABLE = 'roles';
    const API_TOKEN_TABLE = 'user_api_token';
    const PROJECT_LINK_TABLE = 'users_has_projects';

    private $defaultSelect = [
        self::USERS_TABLE.'.id',
        self::USERS_TABLE.'.firstName',
        self::USERS_TABLE.'.insertion',
        self::USERS_TABLE.'.lastName',
        self::USERS_TABLE.'.email',
        self::USERS_TABLE.'.phoneNumber',
        self::USERS_TABLE.'.function',
        self::USERS_TABLE.'.password',
        self::USERS_TABLE.'.role_id',
        self::USERS_TABLE.'.image',
        self::USERS_TABLE.'.token',
        self::USERS_TABLE.'.organisationId',
        self::USERS_TABLE.'.companyId',
        self::ROLES_TABLE.'.name as roleName',
    ];

    /**
     * @var CompaniesHandler
     */
    private $companiesHandler;

    public function __construct(CompaniesHandler $companiesHandler)
    {
        $this->companiesHandler = $companiesHandler;
    }

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

    /**
     * @param string $email
     * @param $organisationId
     * @return User|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function getUserByEmail(string $email, $organisationId)
    {
        try {
            $result = DB::table(self::USERS_TABLE)
                ->select($this->defaultSelect)
                ->join(self::ROLES_TABLE, self::USERS_TABLE.'.role_id', '=', self::ROLES_TABLE.'.id')
                ->where(self::USERS_TABLE.'.email', '=', $email)
                ->where(self::USERS_TABLE.'.organisationId', $organisationId)
                ->first();
            if ( $result === null) {
                return response('Wrong credentials.', 502);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 403);
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

    public function getImage(int $id)
    {
        $image = DB::table(self::USERS_TABLE)
            ->select('image')
            ->where('id', $id)
            ->first();
        return $image;
    }

    public function editUser($postData, $id, $image, $activationToken)
    {
        if ($activationToken) {
            $this->removeActivationToken($id);
        }
        $data = [];

        $image ? $data['image'] = $image->openFile()->fread($image->getSize()) : null;
        foreach ($postData as $key => $value) {
            if ($key === 'password') {
                $data[$key] = password_hash($postData['password'], PASSWORD_DEFAULT);
            } elseif ($key === 'projectsId') {
                // insert the link for the user to the projects.
                $this->linkUserHasProjects(json_decode($postData['projectsId']), $id);
            } else {
                $data[$key] = $value;
            }
        }

        try {
            DB::table(self::USERS_TABLE)
                ->where('id', $id)
                ->update($data);
        } catch (\Exception $e) {
            return response($e->getMessage());
        }

        return $this->getUserById($id);
    }

    public function postUser($postData, $image, $organisationId)
    {
        try {
            $newId = DB::table(self::USERS_TABLE)->insertGetId([
                'firstName' => $postData['firstName'],
                'insertion' => isset($postData['insertion']) ? $postData['insertion'] : NULL,
                'lastName' => $postData['lastName'],
                'email' => $postData['email'],
                'phoneNumber' => $postData['phoneNumber'],
                'function' => $postData['function'],
                'company' => $postData['company'],
                'image' => $image ? $image->openFile()->fread($image->getSize()) : $image,
                'token' => bin2hex(random_bytes(64)),
                'organisationId' => $organisationId,
            ]);
        } catch (\Exception $e) {
            return response($e->getMessage());
        }


        if ( $newId ) {
            // insert the link for the user to the projects.
            foreach (json_decode($postData['projectsId']) as $projectId) {
                DB::table(self::PROJECT_LINK_TABLE)->insert([
                    'userId' => $newId, 'projectId' => $projectId
                ]);
            }

            return $this->getUserById($newId);
        }
        return response('Creating the new user went wrong', 400);
    }

    public function deleteUser(int $userId) {
        try {
            DB::table(self::PROJECT_LINK_TABLE)
                ->where('userId', $userId)
                ->delete();

            DB::table(self::API_TOKEN_TABLE)
                ->where('user_id', $userId)
                ->delete();

            DB::table(self::USERS_TABLE)
                ->where('id', $userId)
                ->delete();
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }
        return json_encode('User deleted');
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

    /**
     * Delete the link between user and project.
     * @param int $userId
     * @param int $projectId
     * @return bool | \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function deleteUserByProjectLink(int $userId, int $projectId)
    {
        try {
            DB::table(self::PROJECT_LINK_TABLE)
                ->where('userId', $userId)
                ->where('projectId', $projectId)
                ->delete();

            //@todo if user has no projects do we want to remove the user? or keep ?
//            if ( !$this->userHasProjects($userId) ) {
//                $this->deleteUser($userId);
//                return json_encode('User deleted');
//            }
            return json_encode('User deleted from project');
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }
    }

    private function linkUserHasProjects($projects, $userId)
    {
        $user = $this->getUserById($userId);
        $userProjects = $user->getProjectsId();

        foreach ($projects as $projectId) {
            $empty = DB::table(self::PROJECT_LINK_TABLE)
                ->where('userId', $userId)
                ->where('projectId', $projectId)
                ->get()
                ->isEmpty();
            // remove chosen projects id from userProjects array so that we can delete the link for the projects
            foreach ($userProjects as $userProjectId) {
                if ($userProjectId === (int)$projectId) {
                    array_splice($userProjects, array_search((int)$projectId, $userProjects), 1);
                }
            }
            // set new link to project.
            if ($empty) {
                DB::table(self::PROJECT_LINK_TABLE)
                    ->insert(['userId' => $userId, 'projectId' => $projectId]);
            }
        }

        // delete the link with the projects that is not selected anymore
        foreach ($userProjects as $projectsToDelete) {
            $this->deleteUserByProjectLink($userId, (int)$projectsToDelete);
        }
    }

    private function userHasProjects(int $id)
    {
        try {
            $result = DB::table(self::PROJECT_LINK_TABLE)
                ->where('userId', $id)
                ->get();
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        return !$result->isEmpty();
    }


    private function removeActivationToken($id)
    {
        try {
            DB::table(self::USERS_TABLE)
                ->where('id', $id)
                ->update(['token' => null]);
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

        $user->setPhoneNumber(isset($data->phoneNumber) ? $data->phoneNumber : null);

        if (isset($data->image)) {
            $user->setImage($data->image);
        }
        if (isset($data->token)) {
            $user->setToken($data->token);
        }

        $user->setProjectsId($this->getProjectsIdFromUser($user));
        $user->setOrganisationId($data->organisationId);
        try {
            $company = $this->companiesHandler->getCompanyById($data->companyId);
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }

        $user->setCompany($company);

        return $user;
    }

}