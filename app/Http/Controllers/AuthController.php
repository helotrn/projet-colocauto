<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateRequest;
use App\Http\Requests\Auth\DeleteRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;
use App\Utils\Traits\ErrorResponseTrait;

class AuthController extends Controller
{
    use ErrorResponseTrait;

    protected $user;
    protected $tokens;
    protected $auth;

    public function __construct(User $user, TokenRepository $tokens, Auth $auth) {
        $this->user = $user;
        $this->tokens = $tokens;
        $this->auth = $auth;
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

        $req = Request::create('/oauth/token', 'POST', $data);
        return app()->handle($req);
    }

    public function logout(Request $request) {
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

        $data = [
            'email' => $email,
            'password' => Hash::make($password),
        ];

        $user = User::create($data);

        if ($user) {
            return response('user registration succesfull', 200);
        } else {
            return $this->sendError("Sorry! Registration failed.", 401);
        }
    }

    public function update(UpdateRequest $request, $id) {

        $email = $request->get('email');
        $password = $request->get('password');

        $data = [
            'email' => $email,
            'password' => Hash::make($password),
        ];

        $user = User::findOrFail($id);
        $user->fill($data);
        $user->save();

        return response('user update succesfull', 200);
    }

    public function delete(DeleteRequest $request, $id) {

        $user = User::findOrFail($id);
        $user->delete();
        return response('user delete succesfull', 200);
    }
}
