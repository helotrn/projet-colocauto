<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UserController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\User\UpdateRequest as UserUpdateRequest;
use App\Models\User;
use App\Utils\Traits\ErrorResponseTrait;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;

class AuthController extends Controller
{
    use ErrorResponseTrait;

    protected $auth;
    protected $tokens;
    protected $user;
    protected $userController;

    public function __construct(User $user, TokenRepository $tokens, Auth $auth, UserController $userController) {
        $this->user = $user;
        $this->tokens = $tokens;
        $this->auth = $auth;
        $this->userController = $userController;
    }

    public function login(LoginRequest $request) {
        $email = $request->get('email');
        $password = $request->get('password');

        $data = [
            'username' => $email,
            'password' => $password,
            'client_id' => config('auth.client.id'),
            'client_secret' => config('auth.client.secret'),
            'grant_type' => 'password'
        ];

        $req = BaseRequest::create('/oauth/token', 'POST', $data);
        return app()->handle($req);
    }

    public function logout(BaseRequest $request) {
        $user = $this->auth->user();

        if (!$user) {
            return response()->json('', 204);
        }

        $id = $user->getKey();

        $tokens = $this->tokens->forUser($id);

        $tokens->map(function ($t) {
            $t->revoke();
        });

        return response()->json('', 204);
    }

    public function register(RegisterRequest $request) {
        $email = $request->get('email');
        $password = $request->get('password');

        $user = new User();
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->save();

        if ($user) {
            $loginRequest = new LoginRequest();
            $loginRequest->setMethod('POST');
            $loginRequest->request->add([
                'email' => $email,
                'password' => $password,
            ]);
            return $this->login($loginRequest);
        }

        return $this->respondWithErrors('Registration error.', 400);
    }

    public function getUser(BaseRequest $request) {
        return $this->userController->retrieve($request, $this->auth->user()->id);
    }

    public function updateUser(UserUpdateRequest $request) {
        $user = $this->auth->user();

        $request->merge([ 'email' => $user->email ]);

        return $this->userController->update($request, $user->id);
    }
}
