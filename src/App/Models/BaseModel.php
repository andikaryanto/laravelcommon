<?php

namespace LaravelCommon\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelCommon\App\Database\Eloquent\Relations\BelongsToRelation;
use ReflectionClass;
use ReflectionProperty;

class BaseModel extends Model
{
    /**
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of created_by
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Set the value of created_by
     *
     * @return  self
     */
    public function setCreatedBy(?User $createdBy)
    {
        $this->createdBy()->associate($createdBy);

        return $this;
    }

    /**
     * @var BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id');
    }

    /**
     * Get the value of created_by
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * Set the value of created_by
     *
     * @return  self
     */
    public function setUpdatedBy(?User $createdBy)
    {
        $this->updatedBy()->associate($createdBy);

        return $this;
    }

    /**
     * @var BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id', 'id');
    }

    /**
     * Get the value of created_at
     */
    public function getCreatedAtUtc(): ?Carbon
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */
    public function setCreatedAtUtc(?Carbon $createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get the value of updated_at
     */
    public function getUpdatedAtUtc(): ?Carbon
    {
        return $this->updated_at;
    }

    /**
     * Set the value of updated_at
     *
     * @return  self
     */
    public function setUpdatedAtUtc(?Carbon $updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    public function isEqualTo(Model $model)
    {
        return get_class($this) == get_class($model) &&
            $this->getId() == $model->getId();
    }

    // public function __get($name)
    // {
    //     if (property_exists($this, $name)) {
    //         if ($this->$name instanceof BelongsToRelation) {
    //             return $this->$name->get();
    //         } else {
    //             return parent::__get($name);
    //         }
    //     } else {
    //         return parent::__get($name);
    //     }
    // }

    public function __call($method, $parameters)
    {
        if (property_exists($this, $method)) {
            $reflectionClass = new ReflectionClass($this);
            $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED);
            foreach ($properties as $property) {
                if (
                    $property->getType() &&
                    $property->getType()->getName() == BelongsToRelation::class &&
                    $property->getName() == $method
                ) {
                    return $this->$method->belongsTo();
                }
            }
            return parent::__call($method, $parameters);
        } else {
            return parent::__call($method, $parameters);
        }
    }
}
