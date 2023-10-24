<?php

namespace LaravelCommon\App\Repositories\User;

use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\Repositories\Repository;
use LaravelCommon\App\ViewModels\User\TokenCollection;
use LaravelCommon\App\ViewModels\User\TokenViewModel;

class TokenRepository extends Repository
{
    /**
    * Constrcutor
    */
    public function __construct()
    {
        parent::__construct(Token::class);
    }

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function collectionClass(): string
    {
        return TokenCollection::class;
    }

    /**
     * @inheritDoc
     *
     * @return stirng
     */
    public function viewModelClass(): string
    {
        return TokenViewModel::class;
    }
}
