<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 6-1-2019
 * Time: 00:11
 */

namespace App\Http\Controllers\Authenticate;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

class Authenticate extends Controller
{
    const USERS_TABLE = 'users';
    const USER_API_TOKEN_TABLE = 'user_api_token';


    public function login(Request $request)
    {
        if($_SESSION['api_token']) {
            return; //rederict to home. Already logged in.
        }

        $email = $request->input('email');
        $password = $request->input('password');

        try {
            $result = DB::table(self::USERS_TABLE)
                ->where('email', '=', $email)
                ->first();
            if ( $result === null) {
                throw new \Exception('The email doesn\'t exist');
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
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

        if (password_verify($password, $user->getPassword())) {
            $token = bin2hex(random_bytes(64));

            DB::table(self::USER_API_TOKEN_TABLE)
                ->insert([
                    'token' => $token,
                    'user_id' => $user->getId(),
                ]);
            $_SESSION['api_token'] = $token;
        } else {
            throw new \Exception('Wrong password.', 401);
        }

    }

}