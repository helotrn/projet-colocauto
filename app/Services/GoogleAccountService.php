<?php

namespace App\Services;

use App\Models\GoogleAccount;
use App\Models\User;

use Laravel\Socialite\Contracts\User as ProviderUser;

class GoogleAccountService
{
    public function createOrGetUser(ProviderUser $providerUser) {
        $account = GoogleAccount::whereProvider('google')
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        }

        $account = new GoogleAccount([
            'provider_user_id' => $providerUser->getId(),
            'provider' => 'google'
        ]);

        $user = User::whereEmail($providerUser->getEmail())->first();

        if (!$user) {
            $user = new User;
            $user->email = $providerUser->getEmail();
            $user->name = $providerUser->getName();
            $user->password = md5('1');
            $user->save();
        }

        $account->user()->associate($user);
        $account->save();

        return $user;
    }
}
