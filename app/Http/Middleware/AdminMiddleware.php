<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-1-2019
 * Time: 15:01
 */

namespace App\Http\Middleware;

use App\Models\Role;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Closure;
use App\Http\Controllers\Roles\RolesController;
use App\Models\User;

class AdminMiddleware
{
    /**
     * The authentication guard factory instance.
     *
     * @var Auth
     */
    protected $auth;

    /**
     * @var RolesController
     */
    protected $rolesController;

    /**
     * Create a new middleware instance.
     *
     * @param Auth $auth
     * @param RolesController $rolesController
     */
    public function __construct(Auth $auth, RolesController $rolesController)
    {
        $this->auth = $auth;
        $this->rolesController = $rolesController;
    }

    public function handle(Request $request, Closure $next, $guard = null)
    {
        /** @var User $user */
        $user = $this->auth->guard($guard)->user();

        if ( !isset($user) ) {
            return response('Forbidden.', 403);
        }

        /** @var Role $role */
        $role = $this->rolesController->getRole($request, $user->getRoleId(), false);

        if ( $role->getName() !== 'admin' )
        {
            return response('Forbidden.', 403);
        }

        return $next($request);
    }

}