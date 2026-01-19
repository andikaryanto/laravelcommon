<?php

namespace LaravelCommon\App\Repositories;

use LaravelCommon\App\Models\Scope;
use LaravelCommon\App\ViewModels\ScopeCollection;
use LaravelCommon\App\ViewModels\ScopeViewModel;

class ScopeRepository extends Repository
{
    /**
     * Constrcutor
     */
    public function __construct()
    {
        parent::__construct(Scope::class);
    }
}
