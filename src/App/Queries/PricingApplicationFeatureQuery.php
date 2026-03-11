<?php

namespace LaravelCommon\App\Queries;

use LaravelCommon\App\Models\Pricing;
use LaravelCommon\App\Models\PricingApplicationFeature;

class PricingApplicationFeatureQuery extends Query
{
    public function identityClass(): string
    {
        return PricingApplicationFeature::class;
    }

    public function wherePrice(Pricing $pricing): PricingApplicationFeatureQuery
    {
        $this->where('pricing_id', $pricing->getId());
        return $this;
    }
}
