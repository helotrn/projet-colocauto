<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UserController;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\User\AddBalanceRequest as UserAddBalanceRequest;
use App\Http\Requests\User\UpdateRequest as UserUpdateRequest;
use App\Models\User;
use App\Services\GoogleAccountService;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Passport\TokenRepository;
use Laravel\Socialite\Facades\Socialite;
use Molotov\Traits\RespondsWithErrors;

class AuthController extends RestController
{
    use RespondsWithErrors, SendsPasswordResetEmails;

    protected $auth;
    protected $tokens;
    protected $user;
    protected $userController;

    public function __construct(
        User $user,
        TokenRepository $tokens,
        Auth $auth,
        UserController $userController
    ) {
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

    public function retrieveUser(Request $request) {
        return $this->userController->retrieve($request, $this->auth->user()->id);
    }

    public function submitUser(Request $request) {
        return $this->userController->submit($request, $this->auth->user()->id);
    }

    public function getUserBalance(Request $request) {
        return $this->userController->getBalance($request, $this->auth->user()->id);
    }

    public function addToUserBalance(Request $request) {
        $request->merge([ 'user_id' => $this->auth->user()->id ]);
        $addBalanceRequest = $request->redirect(UserAddBalanceRequest::class);
        return $this->userController->addToBalance($addBalanceRequest, $this->auth->user()->id);
    }

    public function claimUserBalance(Request $request) {
        return $this->userController->claimBalance($request, $this->auth->user()->id);
    }

    public function updateUser(UserUpdateRequest $request) {
        $user = $this->auth->user();

        $request->merge([ 'email' => $user->email ]);

        return $this->userController->update($request, $user->id);
    }

    public function google() {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(GoogleAccountService $service) {
        $user = $service->createOrGetUser(
            Socialite::driver('google')->stateless()->user()
        );
        auth()->login($user);
        $token = $user->createToken('API Token')->accessToken;
        return redirect()->to('/login/callback?token=' . $token);
    }

    public function passwordRequest(Request $request) {
        return $this->sendResetLinkEmail($request);
    }

    public function passwordReset(ResetPasswordRequest $request) {
        $broker = Password::broker();

        $response = $this->broker()->reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request, $response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    protected function sendResetLinkResponse($response) {
        return $this->respondWithMessage('Token sent.', 200);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response) {
        return $this->respondWithMessage(trans($response), 400);
    }

    protected function sendResetResponse(Request $request, $response) {
        return $this->respondWithMessage('Reset succesful.', 200);
    }

    protected function sendResetFailedResponse(Request $request, $response) {
        return $this->respondWithMessage(trans($response), 400);
    }

    protected function resetPassword($user, $password) {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();
    }
}
