<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User as newUser;
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

                $user = DB::table('users')
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
                    ->where('users.id', $result->user_id)
                    ->first();

                if ($user === null) {
                   throw new \Exception('wrong api token', 401);
                }

                $role = new Role(
                    $user->role_id,
                    $user->roleName
                );

                return new newUser(
                    $user->id,
                    $user->firstName,
                    $user->insertion,
                    $user->lastName,
                    $user->email,
                    $user->function,
                    $user->password,
                    $role
                );
            } else if ($request->input('activationToken')) {
                $user = DB::table('users')
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
                    ->where('users.token', $request->input('activationToken'))
                    ->first();

                if ($user === null) {
                    throw new \Exception('wrong api token', 401);
                }

                $role = new Role(
                    $user->role_id,
                    $user->roleName
                );

                return new newUser(
                    $user->id,
                    $user->firstName,
                    $user->insertion,
                    $user->lastName,
                    $user->email,
                    $user->function,
                    $user->password,
                    $role
                );
            }
            return null;
        });
    }
}
