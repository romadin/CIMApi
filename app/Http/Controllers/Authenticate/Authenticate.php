<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 6-1-2019
 * Time: 00:11
 */

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Mail\MailController;
use App\Http\Handlers\UsersHandler;
use Exception;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Routing\Controller;
use App\Models\User;

class Authenticate extends Controller
{
    const USER_API_TOKEN_TABLE = 'user_api_token';

    /**
     * The authentication guard factory instance.
     *
     * @var Auth
     */
    protected $auth;

    /**
     * @var UsersHandler
     */
    protected $usersHandler;

    /**
     * @var MailController
     */
    private $mailController;


    /**
     * Create a new middleware instance.
     *
     * @param Auth $auth
     * @param UsersHandler $usersHandler
     * @param MailController $mailController
     */
    public function __construct(Auth $auth, UsersHandler $usersHandler, MailController $mailController)
    {
        $this->auth = $auth;
        $this->usersHandler = $usersHandler;
        $this->mailController = $mailController;
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
            $user = $this->usersHandler->getUserByEmail($email, $request->input('organisationId'));
        } catch (Exception $e) {
            return response($e->getMessage(), 404);
        }

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

    public function reset(Request $request)
    {
        if (!$request->input('email')) {
            return response('No email given.', 403);
        }

        if (!$request->input('organisationId')) {
            return response('no organisation given', 403);
        }

        try {
            $user = $this->usersHandler->getUserByEmail($request->input('email'), $request->input('organisationId'));

            $postData = [ 'password' => password_hash(bin2hex(random_bytes(64)), PASSWORD_DEFAULT), 'token' => bin2hex(random_bytes(64))];
            $this->usersHandler->editUser($postData, $user->getId(), null, null);
        } catch (Exception $e) {
            return response($e->getMessage(), 403);
        }
        $this->mailController->sendPasswordRecovery($user->getId());
        return json_encode('password has been reset', 200);
    }

}
