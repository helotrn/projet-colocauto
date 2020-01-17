<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;

class AuthController extends Controller
{
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
}
