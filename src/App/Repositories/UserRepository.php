<?php

namespace LaravelCommon\App\Repositories;

use LaravelCommon\App\Models\User;
use LaravelCommon\App\ViewModels\UserCollection;
use LaravelCommon\App\ViewModels\UserViewModel;

class UserRepository extends Repository
{
 /**
    * Constrcutor
    */
    public function __construct()
    {
        parent::__construct(User::class);
    }

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function collectionClass(): string
    {
        return UserCollection::class;
    }

    /**
     * @inheritDoc
     *
     * @return stirng
     */
    public function viewModelClass(): string
    {
        return UserViewModel::class;
    }
}
