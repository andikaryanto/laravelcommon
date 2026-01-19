<?php

namespace LaravelCommon\App\Services;

use LaravelCommon\App\Models\User;
use Illuminate\Support\Facades\Hash;
use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\Queries\UserQuery;
use LaravelCommon\Utilities\Database\UnitOfWork;

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
     * @var UnitOfWork
     */
    protected UnitOfWork $unitOfWork;

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
     * @param UnitOfWork $entityManager
     * @param Jwt $jwt
     */
    public function __construct(
        UserQuery $userQuery,
        UnitOfWork $unitOfWork,
        Jwt $jwt
    ) {
        $this->userQuery = $userQuery;
        $this->unitOfWork = $unitOfWork;
        $this->jwt = $jwt;
    }

    /**
     * generate user token
     *
     * @param string $username
     * @param string $password
     * @return Token
     */
    public function generateToken(string $email, string $password)
    {

        /**
         * @var User
         */
        $user = $this->userQuery->whereEmail($email)->getIterator()->first();

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

        $this->unitOfWork->persist($userToken);
        $this->unitOfWork->flush();

        return $userToken;
    }
}
