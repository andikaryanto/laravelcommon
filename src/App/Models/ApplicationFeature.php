<?php

namespace LaravelCommon\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationFeature extends BaseModel
{
    use HasFactory;

    /**
     *
     * @return string
     */
    public function getFeatureKey(): string
    {
        return $this->key;
    }

    /**
     *
     * @param string $key
     * @return ApplicationFeature
     */
    public function setFeatureKey(string $key): ApplicationFeature
    {
        $this->key = $key;
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
     * @return ApplicationFeature
     */
    public function setName(string $name): ApplicationFeature
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
     * @return ApplicationFeature
     */
    public function setDescription(?string $description): ApplicationFeature
    {
        $this->description = $description;
        return $this;
    }
}
