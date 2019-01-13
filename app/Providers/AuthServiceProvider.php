<?php

namespace App\Providers;

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

                $user = DB::table('users')->where('id', $result->user_id)->first();

                if ($user === null) {
                   throw new \Exception('wrong api token', 401);
                }

                return new newUser(
                    $user->id,
                    $user->firstName,
                    $user->insertion,
                    $user->lastName,
                    $user->email,
                    $user->password,
                    $user->role_id
                );
            }
            return null;
        });
    }
}
