<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToRelation;

class PricingApplicationFeature extends BaseModel
{
    use HasFactory;

    protected BelongsToRelation $pricing;
    protected BelongsToRelation $applicationFeature;

    public function __construct(array $attributes = [])
    {
        $this->pricing = new BelongsToRelation($this, Pricing::class, 'pricing_id');
        $this->applicationFeature = new BelongsToRelation($this, ApplicationFeature::class, 'application_feature_id');
        parent::__construct($attributes);
    }

    /**
     *
     * @return Pricing
     */
    public function getPricing(): Pricing
    {
        return $this->pricing->get();
    }

    /**
     *
     * @param ?Pricing $pricing
     * @return PricingApplicationFeature
     */
    public function setPricing(?Pricing $pricing): PricingApplicationFeature
    {
        $this->pricing->set($pricing);
        return $this;
    }

    /**
     *
     * @return ApplicationFeature
     */
    public function getApplicationFeature(): ApplicationFeature
    {
        return $this->applicationFeature->get();
    }

    /**
     *
     * @param ?ApplicationFeature $applicationFeature
     * @return PricingApplicationFeature
     */
    public function setApplicationFeature(?ApplicationFeature $applicationFeature): PricingApplicationFeature
    {
        $this->applicationFeature->set($applicationFeature);
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function getIsActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     *
     * @param bool $isActive
     * @return PricingApplicationFeature
     */
    public function setIsActive(bool $isActive): PricingApplicationFeature
    {
        $this->is_active = $isActive;
        return $this;
    }
}
