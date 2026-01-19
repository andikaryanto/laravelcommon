<?php

namespace LaravelCommon\App\Services;

use Carbon\Carbon;
use DateTime;
use Firebase\JWT\JWT as JWTJWT;
use Firebase\JWT\Key;
use LaravelCommon\App\Models\User;
use LaravelCommon\App\Models\User\Token;

class Jwt
{
    /**
     * Create user token
     *
     * @param User $user
     * @return Token
     */
    public function createUserToken(User $user): Token
    {
        $jwtExpiredDay = app('config')->get('common-config')['jwt']['expired_in_days'];
        $jwtExpiredDate = Carbon::now()->addDays($jwtExpiredDay);

        $payload =
            [
                "user_id" => $user->getId(),
                "created_at" => Carbon::now()->format('Y-m-d H:i:s')
            ];

        $token = JWTJWT::encode($payload, env('APP_KEY'), 'HS256');

        $userToken = new Token();
        $userToken->setUser($user);
        $userToken->token = $token;
        $userToken->expired_at = $jwtExpiredDate;

        return $userToken;
    }

    /**
     * Undocumented function
     *
     * @param string $payload
     * @return object
     */
    public function decodeUserToken(string $payload): object
    {
        return JWTJWT::decode($payload, new Key(env('APP_KEY'), 'HS256'));
    }
}
