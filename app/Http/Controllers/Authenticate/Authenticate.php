<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 6-1-2019
 * Time: 00:11
 */

namespace App\Http\Controllers\Authenticate;

use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use App\Models\User;

class Authenticate extends Controller
{
    const USERS_TABLE = 'users';
    const USER_API_TOKEN_TABLE = 'user_api_token';

    /**
     * The authentication guard factory instance.
     *
     * @var Auth
     */
    protected $auth;


    /**
     * Create a new middleware instance.
     *
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }


    public function login(Request $request)
    {
        session_start();
        /** @var User $user */
        $user = $this->auth->guard(null)->user();
        $email = $request->input('email');
        $password = $request->input('password');

        if(!empty($_SESSION['api_token']) && isset($user) && $user->getEmail() === $email) {
            return json_encode(['token' => $_SESSION['api_token']['token'], 'user_id' => $_SESSION['api_token']['user_id']]);
        }

        try {
            $result = DB::table(self::USERS_TABLE)
                ->where('email', '=', $email)
                ->first();
            if ( $result === null) {
                return response('Wrong credentials.', 502);
            }
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }

        $user = new User(
            $result->id,
            $result->firstName,
            $result->insertion,
            $result->lastName,
            $result->email,
            $result->password,
            $result->role_id
        );

        // clear old sessions
        unset($_SESSION['api_token']);

        if (password_verify($password, $user->getPassword())) {
            $token = bin2hex(random_bytes(64));

            DB::table(self::USER_API_TOKEN_TABLE)
                ->insert([
                    'token' => $token,
                    'user_id' => $user->getId(),
                ]);
            $_SESSION['api_token']['token'] = $token;
            $_SESSION['api_token']['user_id'] = $user->getId();
            return json_encode(['token' => $token, 'user_id' => $user->getId()]);
        } else {
            return response('Wrong credentials.', 403);
        }

    }

}