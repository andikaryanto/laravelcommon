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

    public function whereApplicationFeatureKey(string $featureKey): PricingApplicationFeatureQuery
    {
        $this->joinWith(
            'application_features',
            'application_features.id',
            '=',
            'pricing_application_features.application_feature_id'
        );
        $this->where('application_features.key', $featureKey);
        return $this;
    }

    public function whereIsActive(bool $isActive = true): PricingApplicationFeatureQuery
    {
        $this->where('pricing_application_features.is_active', $isActive);
        return $this;
    }
}
