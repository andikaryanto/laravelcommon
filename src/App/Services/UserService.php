<?php

namespace LaravelCommon\App\Services;

use DateTime;
use LaravelCommon\App\Models\User;
use Illuminate\Support\Facades\Hash;
use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\Queries\UserQuery;
use LaravelCommon\Utilities\Database\ModelUnit;

class UserService
{
    /**
     * Undocumented variable
     *
     * @var UserQuery
     */
    protected UserQuery $userQuery;

    /**
     * Undocumented variable
     *
     * @var ModelUnit
     */
    protected ModelUnit $modelUnit;

    /**
     * Undocumented variable
     *
     * @var Jwt
     */
    protected Jwt $jwt;

    /**
     *
     *
     * @param UserQuery $userRepository
     * @param ModelUnit $entityManager
     * @param Jwt $jwt
     */
    public function __construct(
        UserQuery $userQuery,
        ModelUnit $modelUnit,
        Jwt $jwt
    ) {
        $this->userQuery = $userQuery;
        $this->modelUnit = $modelUnit;
        $this->jwt = $jwt;
    }

    /**
     * generate user token
     *
     * @param string $username
     * @param string $password
     * @return Token
     */
    public function generateToken(string $username, string $password)
    {

        /**
         * @var User
         */
        $user = $this->userQuery->whereUsername($username)->getIterator()->first();

        if (empty($user)) {
            return null;
        }

        if (!Hash::check($password, $user->getPassword())) {
            return null;
        }

        return $this->getToken($user);
    }

    /**
     * generate user token
     *
     * @param User
     * @return Token
     */
    public function getToken(User $user): Token
    {

        $userToken = $this->jwt->createUserToken($user);

        $this->modelUnit->persist($userToken);
        $this->modelUnit->flush();

        return $userToken;
    }
}
