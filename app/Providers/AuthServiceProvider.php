<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User as newUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('token')) {
                $result = DB::table('user_api_token')
                    ->where('token', $request->input('token'))->first();
                if ($result === null) {
                   return null;
                }

                $user = $this->getUserFromDatabase('users.id', $result->user_id);
                if ($user === null) {
                   throw new \Exception('wrong api token', 401);
                }

                return $this->makeUser($user);
            } else if ($request->input('activationToken')) {
                $user = $this->getUserFromDatabase('users.token', $request->input('activationToken'));

                if ($user === null) {
                    throw new \Exception('wrong api token', 401);
                }
                return $this->makeUser($user);
            }
            return null;
        });
    }

    private function getUserFromDatabase(string $column, $searchValue)
    {
        return DB::table('users')
            ->select([
                'users.id',
                'users.firstName',
                'users.insertion',
                'users.lastName',
                'users.email',
                'users.function',
                'users.password',
                'users.role_id',
                'roles.name as roleName'
            ])
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where($column, $searchValue)
            ->first();
    }

    private function makeUser($userData): User
    {
        $role = new Role(
            $userData->role_id,
            $userData->roleName
        );

        return new newUser(
            $userData->id,
            $userData->firstName,
            $userData->insertion,
            $userData->lastName,
            $userData->email,
            $userData->function,
            $userData->password,
            $role
        );
    }
}
