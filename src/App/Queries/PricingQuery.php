<?php

namespace LaravelCommon\App\Queries;

use LaravelCommon\App\Models\Pricing;

class PricingQuery extends Query
{
    public function identityClass(): string
    {
        return Pricing::class;
    }
}
