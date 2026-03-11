<?php

namespace LaravelCommon\App\Repositories;

use LaravelCommon\App\Models\PricingApplicationFeature;

class PricingApplicationFeatureRepository extends Repository
{
    /**
    * Constrcutor
    */
    public function __construct()
    {
        parent::__construct(PricingApplicationFeature::class);
    }
}
