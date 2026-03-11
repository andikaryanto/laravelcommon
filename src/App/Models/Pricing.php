<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToManyRelation;

class Pricing extends BaseModel
{
    use HasFactory;

    protected BelongsToManyRelation $applicationFeatures;

    public function __construct(array $attributes = [])
    {
        $this->applicationFeatures = new BelongsToManyRelation(
            $this,
            ApplicationFeature::class,
            'pricing_application_features'
        );
        parent::__construct($attributes);
    }

    /**
     *
     * @return Collection
     */
    public function getApplicationFeatures()
    {
        return $this->applicationFeatures->getIterator();
    }

    /**
     *
     * @param Collection $applicationFeatures
     * @return BelongsToManyRelation
     */
    public function setApplicationFeatures(Collection $applicationFeatures): BelongsToManyRelation
    {
        return $this->applicationFeatures->set($applicationFeatures);
    }

    /**
     *
     * @param ApplicationFeature $applicationFeature
     * @return Pricing
     */
    public function addApplicationFeature(ApplicationFeature $applicationFeature): Pricing
    {
        $this->applicationFeatures->add($applicationFeature);
        return $this;
    }

    /**
     *
     * @param ApplicationFeature $applicationFeature
     * @return Pricing
     */
    public function removeApplicationFeature(ApplicationFeature $applicationFeature): Pricing
    {
        $this->applicationFeatures->remove($applicationFeature);
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     * @return Pricing
     */
    public function setName(string $name): Pricing
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     *
     * @param ?string $description
     * @return Pricing
     */
    public function setDescription(?string $description): Pricing
    {
        $this->description = $description;
        return $this;
    }
}
