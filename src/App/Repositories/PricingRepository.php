<?php

namespace LaravelCommon\App\Repositories;

use LaravelCommon\App\Models\Pricing;

class PricingRepository extends Repository
{
    /**
    * Constrcutor
    */
    public function __construct()
    {
        parent::__construct(Pricing::class);
    }
}
